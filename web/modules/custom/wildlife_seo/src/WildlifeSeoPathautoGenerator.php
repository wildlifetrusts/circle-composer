<?php

namespace Drupal\wildlife_seo;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\Token;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\pathauto\AliasStorageHelperInterface;
use Drupal\pathauto\AliasUniquifierInterface;
use Drupal\pathauto\MessengerInterface;
use Drupal\pathauto\PathautoGenerator;
use Drupal\token\TokenEntityMapperInterface;

/**
 * Provides methods for generating path aliases.
 */
class WildlifeSeoPathautoGenerator extends PathautoGenerator {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection;
   */
  protected $database;

  /**
   * Creates a new Pathauto manager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   * @param \Drupal\pathauto\AliasCleanerInterface $alias_cleaner
   *   The alias cleaner.
   * @param \Drupal\pathauto\AliasStorageHelperInterface $alias_storage_helper
   *   The alias storage helper.
   * @param AliasUniquifierInterface $alias_uniquifier
   *   The alias uniquifier.
   * @param MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\token\TokenEntityMapperInterface $token_entity_mappper
   *   The token entity mapper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, Token $token, AliasCleanerInterface $alias_cleaner, AliasStorageHelperInterface $alias_storage_helper, AliasUniquifierInterface $alias_uniquifier, MessengerInterface $messenger, TranslationInterface $string_translation, TokenEntityMapperInterface $token_entity_mappper, EntityTypeManagerInterface $entity_type_manager, Connection $database) {
    parent::__construct($config_factory, $module_handler, $token, $alias_cleaner, $alias_storage_helper, $alias_uniquifier, $messenger, $string_translation, $token_entity_mappper, $entity_type_manager);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntityAlias(EntityInterface $entity, $op, array $options = array()) {
    $result = parent::updateEntityAlias($entity, $op, $options);

    $type = $entity->getEntityTypeId();

    if ($type == 'taxonomy_term') {
      if (in_array($entity->bundle(), ['species', 'habitat'])) {
        foreach ($this->loadTaggedNodes($entity->id()) as $node) {
          $this->updateEntityAlias($node, $op, $options);
        }
      }
    }

    return $result;
  }

  /**
   * @param int $tid
   *   Term ID to retrieve nodes for.
   *
   * @return EntityInterface[]
   *   An array of node objects that are the tagged with the term $tid.
   *
   * @return \Drupal\node\NodeInterface[]
   *   An array of term objects that are the children of the term $tid.
   */
  protected function loadTaggedNodes($tid) {
    $query = $this->database->select('taxonomy_index', 'ti');
    $query->fields('ti', ['nid']);
    $query->condition('ti.tid', $tid);
    $nodes = $query->execute()->fetchAllKeyed(0, 0);

    return $this->entityTypeManager->getStorage('node')->loadMultiple($nodes);
  }

}
