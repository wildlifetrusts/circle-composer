<?php

/**
 * @file
 * Contains \Drupal\wildlife_content\EventSubscriber\WildlifeContentEventSubscriber.
 */

namespace Drupal\wildlife_content\EventSubscriber;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate_plus\Entity\Migration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WildlifeContentEventSubscriber
 * @package Drupal\wildlife_content\EventSubscriber
 */
class WildlifeContentEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE][] = ['onPostRowSave'];
    return $events;
  }

  /**
   * Callback to set the default front page setting when the example homepage
   * node is imported.
   *
   * @param MigratePostRowSaveEvent $event
   */
  public function onPostRowSave(MigratePostRowSaveEvent $event) {
    // Migration object being imported.
    $migration = $event->getMigration();

    // Loads a Taxonomy Term by UUID.
    $footer_flexible_block = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', '7570d67e-322e-4519-925d-15c4de33530c');
    // The unique destination ID, as an array (accommodating multi-column
    // keys), of the item just imported.
    $destination_id_values = $event->getDestinationIdValues();

    switch ($migration->id()) {
      case 'paragraph_quick_links_national':
        if (!empty($footer_flexible_block)) {
          $first_item = $footer_flexible_block->get('field_flexible_block_content_1')->first();

          if (empty($first_item)) {
            $values = [
              'target_id' => $destination_id_values['id'],
              'target_revision_id' => $destination_id_values['revision_id'],
            ];
            $footer_flexible_block->get('field_flexible_block_title_1')->setValue('Column 1 title');
            $footer_flexible_block->get('field_flexible_block_content_1')->setValue(array($values));
          }
        }

        break;

      case 'paragraph_rich_text_national':
        if (!empty($footer_flexible_block)) {
          $first_item = $footer_flexible_block->get('field_flexible_block_content_2')->first();

          if (empty($first_item)) {
            $values = [
              'target_id' => $destination_id_values['id'],
              'target_revision_id' => $destination_id_values['revision_id'],
            ];

            $footer_flexible_block->get('field_flexible_block_title_2')->setValue('Column 2 title');
            $footer_flexible_block->get('field_flexible_block_content_2')->setValue(array($values));
          }
        }
        break;

      case 'paragraph_linked_logos_national':
        if (!empty($footer_flexible_block)) {
          $first_item = $footer_flexible_block->get('field_flexible_block_content_3')->first();

          if (empty($first_item)) {
            $values = [
              'target_id' => $destination_id_values['id'],
              'target_revision_id' => $destination_id_values['revision_id'],
            ];

            $footer_flexible_block->get('field_flexible_block_title_3')->setValue('Column 3 title');
            $footer_flexible_block->get('field_flexible_block_content_3')->setValue(array($values));
          }
        }
        break;
    }

    if (!empty($footer_flexible_block)) {
      $footer_flexible_block->save();
    }
  }
}
