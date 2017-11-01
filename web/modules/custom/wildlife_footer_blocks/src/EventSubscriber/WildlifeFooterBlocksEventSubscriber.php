<?php

/**
 * @file
 * Contains \Drupal\wildlife_footer_blocks\EventSubscriber\WildlifeFooterBlocksEventSubscriber.
 */

namespace Drupal\wildlife_footer_blocks\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WildlifeFooterBlocksEventSubscriber
 * @package Drupal\wildlife_footer_blocks\EventSubscriber
 */
class WildlifeFooterBlocksEventSubscriber implements EventSubscriberInterface {
  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The source storage used to discover configuration changes.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $sourceStorage;

  /**
   * Creates a config snapshot.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onConfigImporterImport(ConfigImporterEvent $event) {
    $create_list = $event->getConfigImporter()->getStorageComparer()->getChangelist('create');

    if (in_array('block_content.type.flexible_block', $create_list)) {
      $this->createDefaultFlexibleBlock();
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT][] = array('onConfigImporterImport', 40);
    return $events;
  }


  private function createDefaultFlexibleBlock() {
    // Set the block title for later use.
    $block_title = t('Footer flexible blocks');
    $uuid = '7570d67e-322e-4519-925d-15c4de33530c';

    // Grab a block entity manager from EntityManager service
    $blockEntityManager = \Drupal::service('entity.manager')
      ->getStorage('block_content');

    $flexible_block = $blockEntityManager->create(array(
      'type' => 'flexible_block'
    ));

    $flexible_block->info = $block_title;
    $flexible_block->uuid = $uuid;

    $flexible_block->save();
  }
}
