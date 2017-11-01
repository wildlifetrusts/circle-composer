<?php

namespace Drupal\wildlife_location;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Location entity.
 *
 * @see \Drupal\wildlife_location\Entity\Location.
 */
class LocationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\wildlife_location\Entity\LocationInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished location entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published location entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit location entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete location entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add location entities');
  }

}
