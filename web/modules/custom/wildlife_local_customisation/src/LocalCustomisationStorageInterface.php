<?php

namespace Drupal\wildlife_local_customisation;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface LocalCustomisationStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Local customisation revision IDs for a specific Local customisation.
   *
   * @param \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface $entity
   *   The Local customisation entity.
   *
   * @return int[]
   *   Local customisation revision IDs (in ascending order).
   */
  public function revisionIds(LocalCustomisationInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Local customisation author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Local customisation revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface $entity
   *   The Local customisation entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(LocalCustomisationInterface $entity);

  /**
   * Unsets the language for all Local customisation with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
