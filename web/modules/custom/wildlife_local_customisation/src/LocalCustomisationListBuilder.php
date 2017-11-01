<?php

namespace Drupal\wildlife_local_customisation;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Defines a class to build a listing of Local customisation entities.
 *
 * @ingroup wildlife_local_customisation
 */
class LocalCustomisationListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['associated_content'] = $this->t('Associated content');
    $header['content_type'] = $this->t('Content type');
    $header['blacklisted'] = $this->t('Blacklisted?');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\wildlife_local_customisation\Entity\LocalCustomisation */
    $row['id'] = $entity->id();

    $associated_node = $entity->getAssociatedNode();
    $row['associated_content'] = $this->l(
      $associated_node->label(),
      $associated_node->toUrl()
    );

    $entity_manager = \Drupal::entityTypeManager();
    $entity_definition = $entity_manager->getDefinition('node');

    $bundle_label = \Drupal::entityTypeManager()
      ->getStorage($entity_definition->getBundleEntityType())
      ->load($associated_node->bundle())
      ->label();

    $row['content_type'] = $bundle_label;

    $blacklist_value = $entity->get('field_local_blacklist')->first()->getValue();
    $row['blacklisting'] = $blacklist_value['value'] ? $this->t('Yes') : $this->t('No');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no @labels yet.', ['@label' => $this->entityType->getLabel()]);

    return $build;
  }
}
