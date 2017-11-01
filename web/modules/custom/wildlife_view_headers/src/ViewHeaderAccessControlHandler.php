<?php

namespace Drupal\wildlife_view_headers;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the View header entity.
 *
 * @see \Drupal\wildlife_view_headers\Entity\ViewHeader.
 */
class ViewHeaderAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished view header entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published view header entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit view header entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete view header entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add view header entities');
  }

}
