<?php

namespace Drupal\wildlife_local_customisation;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Local customisation entity.
 *
 * @see \Drupal\wildlife_local_customisation\Entity\LocalCustomisation.
 */
class LocalCustomisationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished local customisation entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published local customisation entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit local customisation entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete local customisation entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add local customisation entities');
  }

}
