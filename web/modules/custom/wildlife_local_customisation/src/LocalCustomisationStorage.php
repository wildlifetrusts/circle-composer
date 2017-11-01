<?php

namespace Drupal\wildlife_local_customisation;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface;

/**
 * Defines the storage handler class for Local customisation entities.
 *
 * This extends the base storage class, adding required special handling for
 * Local customisation entities.
 *
 * @ingroup wildlife_local_customisation
 */
class LocalCustomisationStorage extends SqlContentEntityStorage implements LocalCustomisationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(LocalCustomisationInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {local_customisation_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {local_customisation_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(LocalCustomisationInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {local_customisation_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('local_customisation_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
