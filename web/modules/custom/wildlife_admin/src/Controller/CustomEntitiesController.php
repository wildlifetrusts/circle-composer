<?php

namespace Drupal\wildlife_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns a response for the CustomEntities controller.
 */
class CustomEntitiesController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a CustomEntitiesController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $entity_type_manager = $container->get('entity_type.manager')
    );
  }

  /**
   * Return an array of Wildlife custom entity types.
   *
   * @return array
   */
  private function getCustomEntityTypes() {
    $content_entity_types = array();
    $entity_type_definations = $this->entityTypeManager->getDefinitions();
    /* @var $definition EntityTypeInterface */
    foreach ($entity_type_definations as $definition) {
      if ($definition instanceof ContentEntityType) {
        $provider = $definition->getProvider();

        if (strpos($provider, 'wildlife_') === 0) {
          $content_entity_types[] = $definition;
        }
      }
    }

    return $content_entity_types;
  }

  /**
   * Displays entity settings links for custom entities.
   */
  public function customEntitiesList() {
    $entities = $this->getCustomEntityTypes();
    $content = array();

    /** @var \Drupal\Core\Entity\ContentEntityType $entity */
    foreach ($entities as $entity) {
      $content[$entity->id()] = $entity;
    }

    $build = [
      '#theme' => 'custom_entities_list',
      '#content' => $content,
    ];

    return $build;
  }
}


