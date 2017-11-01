<?php

namespace Drupal\wildlife_location\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\wildlife_location\Entity\LocationInterface;

/**
 * Class LocationController.
 *
 *  Returns responses for Location routes.
 */
class LocationController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Location  revision.
   *
   * @param int $location_revision
   *   The Location  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($location_revision) {
    $location = $this->entityManager()->getStorage('location')->loadRevision($location_revision);
    $view_builder = $this->entityManager()->getViewBuilder('location');

    return $view_builder->view($location);
  }

  /**
   * Page title callback for a Location  revision.
   *
   * @param int $location_revision
   *   The Location  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($location_revision) {
    $location = $this->entityManager()->getStorage('location')->loadRevision($location_revision);
    return $this->t('Revision of %title from %date', ['%title' => $location->label(), '%date' => format_date($location->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Location .
   *
   * @param \Drupal\wildlife_location\Entity\LocationInterface $location
   *   A Location  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(LocationInterface $location) {
    $account = $this->currentUser();
    $langcode = $location->language()->getId();
    $langname = $location->language()->getName();
    $languages = $location->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $location_storage = $this->entityManager()->getStorage('location');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $location->label()]) : $this->t('Revisions for %title', ['%title' => $location->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all location revisions") || $account->hasPermission('administer location entities')));
    $delete_permission = (($account->hasPermission("delete all location revisions") || $account->hasPermission('administer location entities')));

    $rows = [];

    $vids = $location_storage->revisionIds($location);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\wildlife_location\LocationInterface $revision */
      $revision = $location_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $location->getRevisionId()) {
          $link = $this->l($date, new Url('entity.location.revision', ['location' => $location->id(), 'location_revision' => $vid]));
        }
        else {
          $link = $location->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.location.translation_revert', ['location' => $location->id(), 'location_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.location.revision_revert', ['location' => $location->id(), 'location_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.location.revision_delete', ['location' => $location->id(), 'location_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['location_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
