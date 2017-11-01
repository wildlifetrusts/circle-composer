<?php

namespace Drupal\wildlife_view_headers\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\wildlife_view_headers\Entity\ViewHeaderInterface;

/**
 * Class ViewHeaderController.
 *
 *  Returns responses for View header routes.
 *
 * @package Drupal\wildlife_view_headers\Controller
 */
class ViewHeaderController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a View header  revision.
   *
   * @param int $view_header_revision
   *   The View header  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($view_header_revision) {
    $view_header = $this->entityManager()->getStorage('view_header')->loadRevision($view_header_revision);
    $view_builder = $this->entityManager()->getViewBuilder('view_header');

    return $view_builder->view($view_header);
  }

  /**
   * Page title callback for a View header  revision.
   *
   * @param int $view_header_revision
   *   The View header  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($view_header_revision) {
    $view_header = $this->entityManager()->getStorage('view_header')->loadRevision($view_header_revision);
    return $this->t('Revision of %title from %date', ['%title' => $view_header->label(), '%date' => format_date($view_header->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a View header .
   *
   * @param \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface $view_header
   *   A View header  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ViewHeaderInterface $view_header) {
    $account = $this->currentUser();
    $langcode = $view_header->language()->getId();
    $langname = $view_header->language()->getName();
    $languages = $view_header->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $view_header_storage = $this->entityManager()->getStorage('view_header');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $view_header->label()]) : $this->t('Revisions for %title', ['%title' => $view_header->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all view header revisions") || $account->hasPermission('administer view header entities')));
    $delete_permission = (($account->hasPermission("delete all view header revisions") || $account->hasPermission('administer view header entities')));

    $rows = [];

    $vids = $view_header_storage->revisionIds($view_header);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\wildlife_view_headers\ViewHeaderInterface $revision */
      $revision = $view_header_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $view_header->getRevisionId()) {
          $link = $this->l($date, new Url('entity.view_header.revision', ['view_header' => $view_header->id(), 'view_header_revision' => $vid]));
        }
        else {
          $link = $view_header->link($date);
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
              Url::fromRoute('entity.view_header.translation_revert', ['view_header' => $view_header->id(), 'view_header_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.view_header.revision_revert', ['view_header' => $view_header->id(), 'view_header_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.view_header.revision_delete', ['view_header' => $view_header->id(), 'view_header_revision' => $vid]),
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

    $build['view_header_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
