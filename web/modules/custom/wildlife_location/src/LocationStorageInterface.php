<?php

namespace Drupal\wildlife_location;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface LocationStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Location revision IDs for a specific Location.
   *
   * @param \Drupal\wildlife_location\Entity\LocationInterface $entity
   *   The Location entity.
   *
   * @return int[]
   *   Location revision IDs (in ascending order).
   */
  public function revisionIds(LocationInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Location author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Location revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\wildlife_location\Entity\LocationInterface $entity
   *   The Location entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(LocationInterface $entity);

  /**
   * Unsets the language for all Location with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
