<?php

namespace Drupal\wildlife_location;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\wildlife_location\Entity\LocationInterface;

/**
 * Defines the storage handler class for Location entities.
 *
 * This extends the base storage class, adding required special handling for
 * Location entities.
 *
 * @ingroup wildlife_location
 */
class LocationStorage extends SqlContentEntityStorage implements LocationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(LocationInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {location_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {location_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(LocationInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {location_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('location_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
