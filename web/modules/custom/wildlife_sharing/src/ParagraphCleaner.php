<?php

namespace Drupal\wildlife_sharing;

class ParagraphCleaner {

  public static function removeOrphanParagraphs() {
    $batch = [
      'title' => t('Processing ParagraphCleaner'),
      'operations' => [
        [[ParagraphCleaner::class, 'removeOrphanParagraphsCallback'], []]
      ],
      'progressive' => TRUE,
    ];
    batch_set($batch);
  }

  /**
   * Delete any paragraph items where the parent item no longer exists.
   *
   * Based on the update hook form https://www.drupal.org/node/2711563.
   */
  public static function removeOrphanParagraphsCallback(&$context) {
    $sandbox = &$context['sandbox'];
    $delete_ids = NULL;
    if (!isset($sandbox['progress'])) {
      $sandbox['progress'] = 0;
      $sandbox['max'] = \Drupal::entityQuery('paragraph')
        ->count()
        ->execute();
    }
    $ids = \Drupal::entityQuery('paragraph')
      ->range($sandbox['progress'], \Drupal\Core\Site\Settings::get('paragraph_limit', 50))
      ->sort('id', 'ASC')
      ->execute();
    foreach ($ids as $id) {
      $paragraph_id = \Drupal::entityQuery('paragraph')
        ->condition('parent_type', NULL, 'IS NOT NULL')
        ->condition('id', $id)
        ->execute();
      if (!$paragraph_id) {
        $delete_ids[] = $id;
      }
    }
    $sandbox['progress'] += \Drupal\Core\Site\Settings::get('paragraph_limit', 50);
    if ($delete_ids) {
      $storage = \Drupal::entityTypeManager()->getStorage('paragraph');
      $entities = $storage->loadMultiple($delete_ids);
      $storage->delete($entities);
    }
    $context['finished'] = empty($sandbox['max']) ? 1 : ($sandbox['progress'] / $sandbox['max']);
  }

}
