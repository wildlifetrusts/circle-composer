<?php

namespace Drupal\wildlife_local_customisation\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface;

/**
 * Class LocalCustomisationController.
 *
 *  Returns responses for Local customisation routes.
 *
 * @package Drupal\wildlife_local_customisation\Controller
 */
class LocalCustomisationController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Local customisation  revision.
   *
   * @param int $local_customisation_revision
   *   The Local customisation  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($local_customisation_revision) {
    $local_customisation = $this->entityManager()->getStorage('local_customisation')->loadRevision($local_customisation_revision);
    $view_builder = $this->entityManager()->getViewBuilder('local_customisation');

    return $view_builder->view($local_customisation);
  }

  /**
   * Page title callback for a Local customisation  revision.
   *
   * @param int $local_customisation_revision
   *   The Local customisation  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($local_customisation_revision) {
    $local_customisation = $this->entityManager()->getStorage('local_customisation')->loadRevision($local_customisation_revision);
    return $this->t('Revision of %title from %date', ['%title' => $local_customisation->label(), '%date' => format_date($local_customisation->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Local customisation .
   *
   * @param \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface $local_customisation
   *   A Local customisation  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(LocalCustomisationInterface $local_customisation) {
    $account = $this->currentUser();
    $langcode = $local_customisation->language()->getId();
    $langname = $local_customisation->language()->getName();
    $languages = $local_customisation->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $local_customisation_storage = $this->entityManager()->getStorage('local_customisation');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $local_customisation->label()]) : $this->t('Revisions for %title', ['%title' => $local_customisation->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all local customisation revisions") || $account->hasPermission('administer local customisation entities')));
    $delete_permission = (($account->hasPermission("delete all local customisation revisions") || $account->hasPermission('administer local customisation entities')));

    $rows = [];

    $vids = $local_customisation_storage->revisionIds($local_customisation);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\wildlife_local_customisation\LocalCustomisationInterface $revision */
      $revision = $local_customisation_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $local_customisation->getRevisionId()) {
          $link = $this->l($date, new Url('entity.local_customisation.revision', ['local_customisation' => $local_customisation->id(), 'local_customisation_revision' => $vid]));
        }
        else {
          $link = $local_customisation->link($date);
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
              Url::fromRoute('entity.local_customisation.translation_revert', ['local_customisation' => $local_customisation->id(), 'local_customisation_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.local_customisation.revision_revert', ['local_customisation' => $local_customisation->id(), 'local_customisation_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.local_customisation.revision_delete', ['local_customisation' => $local_customisation->id(), 'local_customisation_revision' => $vid]),
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

    $build['local_customisation_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
