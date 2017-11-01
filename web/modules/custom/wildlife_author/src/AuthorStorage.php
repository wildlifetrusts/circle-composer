<?php

namespace Drupal\wildlife_author;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\wildlife_author\Entity\AuthorInterface;

/**
 * Defines the storage handler class for Author entities.
 *
 * This extends the base storage class, adding required special handling for
 * Author entities.
 *
 * @ingroup wildlife_author
 */
class AuthorStorage extends SqlContentEntityStorage implements AuthorStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AuthorInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {author_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {author_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(AuthorInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {author_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('author_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

  /**
   * Fetch the author ID from a given blog ID.
   *
   * @param string $blog_id
   *   The blog ID.
   *
   * @return mixed
   *   The Author ID if there is one.
   */
  public function getAuthorByBlogId($blog_id) {
    return $this->database->query('SELECT id FROM {author_field_data} WHERE blog_id = :blog_id',  [':blog_id' => $blog_id])
      ->fetchCol();
  }

}
