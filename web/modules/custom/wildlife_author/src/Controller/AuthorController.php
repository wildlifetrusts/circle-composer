<?php

namespace Drupal\wildlife_author\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\wildlife_author\Entity\AuthorInterface;

/**
 * Class AuthorController.
 *
 *  Returns responses for Author routes.
 *
 * @package Drupal\wildlife_author\Controller
 */
class AuthorController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Author  revision.
   *
   * @param int $author_revision
   *   The Author  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($author_revision) {
    $author = $this->entityManager()->getStorage('author')->loadRevision($author_revision);
    $view_builder = $this->entityManager()->getViewBuilder('author');

    return $view_builder->view($author);
  }

  /**
   * Page title callback for a Author  revision.
   *
   * @param int $author_revision
   *   The Author  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($author_revision) {
    $author = $this->entityManager()->getStorage('author')->loadRevision($author_revision);
    return $this->t('Revision of %title from %date', ['%title' => $author->label(), '%date' => format_date($author->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Author .
   *
   * @param \Drupal\wildlife_author\Entity\AuthorInterface $author
   *   A Author  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AuthorInterface $author) {
    $account = $this->currentUser();
    $langcode = $author->language()->getId();
    $langname = $author->language()->getName();
    $languages = $author->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $author_storage = $this->entityManager()->getStorage('author');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $author->label()]) : $this->t('Revisions for %title', ['%title' => $author->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all author revisions") || $account->hasPermission('administer author entities')));
    $delete_permission = (($account->hasPermission("delete all author revisions") || $account->hasPermission('administer author entities')));

    $rows = [];

    $vids = $author_storage->revisionIds($author);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\wildlife_author\AuthorInterface $revision */
      $revision = $author_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $author->getRevisionId()) {
          $link = $this->l($date, new Url('entity.author.revision', ['author' => $author->id(), 'author_revision' => $vid]));
        }
        else {
          $link = $author->link($date);
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
              Url::fromRoute('entity.author.translation_revert', ['author' => $author->id(), 'author_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.author.revision_revert', ['author' => $author->id(), 'author_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.author.revision_delete', ['author' => $author->id(), 'author_revision' => $vid]),
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

    $build['author_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
