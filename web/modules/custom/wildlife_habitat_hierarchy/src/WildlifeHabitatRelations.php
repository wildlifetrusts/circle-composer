<?php

namespace Drupal\wildlife_habitat_hierarchy;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_hierarchy\Storage\EntityTreeNodeMapperInterface;
use Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory;
use Drupal\entity_hierarchy\Storage\NestedSetStorageFactory;
use Drupal\node\Entity\Node;

/**
 * Retrieves Habitat node relations.
 *
 * @package Drupal\wildlife_habitat_hierarchy
 */
class WildlifeHabitatRelations {
  use StringTranslationTrait;

  /**
   * Nested set storage factory.
   *
   * @var \Drupal\entity_hierarchy\Storage\NestedSetStorageFactory
   */
  protected $nestedSetStorageFactory;

  /**
   * Nested set node key factory.
   *
   * @var \Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory
   */
  protected $nodeKeyFactory;

  /**
   * Tree node mapper.
   *
   * @var \Drupal\entity_hierarchy\Storage\EntityTreeNodeMapperInterface
   */
  protected $entityTreeNodeMapper;

  /**
   * Entity Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityTypeManager;

  /**
   * WildlifeHabitatRelations constructor.
   *
   * @param NestedSetStorageFactory $nestedSetStorageFactory
   * @param NestedSetNodeKeyFactory $nodeKeyFactory
   * @param EntityTreeNodeMapperInterface $entityTreeNodeMapper
   * @param EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(NestedSetStorageFactory $nestedSetStorageFactory, NestedSetNodeKeyFactory $nodeKeyFactory, EntityTreeNodeMapperInterface $entityTreeNodeMapper, EntityTypeManagerInterface $entityTypeManager) {
    $this->nestedSetStorageFactory = $nestedSetStorageFactory;
    $this->nodeKeyFactory = $nodeKeyFactory;
    $this->entityTreeNodeMapper = $entityTreeNodeMapper;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the children or siblings of a habitat node.
   *
   * @param Node $current_node
   * @return array
   *   An item list.
   */
  public function getHabitatRelations($current_node) {
    $build = [];

    $cache = (new CacheableMetadata())->addCacheableDependency($current_node);
    $fieldName = 'field_habitat_parent';

    /** @var \PNX\NestedSet\Node[] $relations */
    /** @var \PNX\NestedSet\NestedSetInterface $storage */
    $storage = $this->nestedSetStorageFactory->get($fieldName, $current_node->getEntityTypeId());

    // First, try to find children and display those if there are some.
    $relations = $storage->findChildren($this->nodeKeyFactory->fromEntity($current_node));
    $childEntities = $this->entityTreeNodeMapper->loadAndAccessCheckEntitysForTreeNodes($current_node->getEntityTypeId(), $relations, $cache);

    // If there are no child entities, try to get siblings.
    if (!$childEntities->count()) {
      $parent = $storage->findParent($this->nodeKeyFactory->fromEntity($current_node));

      if ($parent) {
        $parent_node = Node::load($parent->getId());
        $relations = $storage->findChildren($this->nodeKeyFactory->fromEntity($parent_node));
        $childEntities = $this->entityTreeNodeMapper->loadAndAccessCheckEntitysForTreeNodes($current_node->getEntityTypeId(), $relations, $cache);

        // If there are no siblings (or children), don't show the block.
        if (!$childEntities->count()) {
          return $build;
        }
      }
    }

    // Loop through and render a listing of child or sibling habitats.
    $habitats_list = [];
    $view_builder = $this->entityTypeManager->getViewBuilder('node');

    foreach ($relations as $weight => $node) {
      if ($node->getId() == $current_node->id()) {
        // Never show the current node as part of the list.
        continue;
      }

      if (!$childEntities->contains($node)) {
        // Doesn't exist or is access hidden.
        continue;
      }
      /** @var \Drupal\Core\Entity\ContentEntityInterface $childEntity */
      $childEntity = $childEntities->offsetGet($node);

      if (!$childEntity->isDefaultRevision()) {
        // We only update default revisions here.
        continue;
      }

      if (\Drupal::moduleHandler()->moduleExists('wildlife_local_customisation')) {
        // Check that the content is not blacklisted.
        $is_blacklisted = _wildlife_local_customisation_content_is_blacklisted($childEntity);

        if ($is_blacklisted) {
          continue;
        }
      }

      // Check current language being viewed and get teasers in that language.
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $node_view = $view_builder->view($childEntity, 'teaser', $language);
      $habitats_list[] = [
        '#markup' => render($node_view),
        '#wrapper_attributes' => ['class' => ['field__item']],
      ];
    }

    if (!empty($habitats_list)) {
      $build = [
        '#type' => 'container',
        '#attributes' => ['class' => ['node__related', 'is-unconstrained']],
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $this->t('Related Habitats'),
        ],
        'content' => [
          '#theme' => 'item_list',
          '#items' => $habitats_list,
          '#attributes' => ['class' => ['field--related-habitats']],
        ],
      ];
    }

    return $build;
  }
}
