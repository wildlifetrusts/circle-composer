<?php

namespace Drupal\wildlife_view_headers;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\wildlife_view_headers\Entity\ViewHeaderInterface;

/**
 * Defines the storage handler class for View header entities.
 *
 * This extends the base storage class, adding required special handling for
 * View header entities.
 *
 * @ingroup wildlife_view_headers
 */
class ViewHeaderStorage extends SqlContentEntityStorage implements ViewHeaderStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ViewHeaderInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {view_header_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {view_header_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ViewHeaderInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {view_header_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('view_header_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
