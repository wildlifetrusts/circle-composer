<?php

namespace Drupal\wildlife_view_headers;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface ViewHeaderStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of View header revision IDs for a specific View header.
   *
   * @param \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface $entity
   *   The View header entity.
   *
   * @return int[]
   *   View header revision IDs (in ascending order).
   */
  public function revisionIds(ViewHeaderInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as View header author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   View header revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface $entity
   *   The View header entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ViewHeaderInterface $entity);

  /**
   * Unsets the language for all View header with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
