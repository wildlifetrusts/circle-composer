<?php

namespace Drupal\wildlife_local_customisation\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Local customisation entities.
 *
 * @ingroup wildlife_local_customisation
 */
interface LocalCustomisationInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Local customisation name.
   *
   * @return string
   *   Name of the Local customisation.
   */
  public function getName();

  /**
   * Sets the Local customisation name.
   *
   * @param string $name
   *   The Local customisation name.
   *
   * @return \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface
   *   The called Local customisation entity.
   */
  public function setName($name);

  /**
   * Gets the Local customisation creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Local customisation.
   */
  public function getCreatedTime();

  /**
   * Sets the Local customisation creation timestamp.
   *
   * @param int $timestamp
   *   The Local customisation creation timestamp.
   *
   * @return \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface
   *   The called Local customisation entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Local customisation published status indicator.
   *
   * Unpublished Local customisation are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Local customisation is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Local customisation.
   *
   * @param bool $published
   *   TRUE to set this Local customisation to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface
   *   The called Local customisation entity.
   */
  public function setPublished($published);

  /**
   * Gets the Local customisation revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Local customisation revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface
   *   The called Local customisation entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Local customisation revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Local customisation revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\wildlife_local_customisation\Entity\LocalCustomisationInterface
   *   The called Local customisation entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Get the node associated with this local customisation.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getAssociatedNode();

  /**
   * Returns the Local customisation blacklisted status indicator.
   *
   * Blacklisted Local customisation are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Local customisation is blacklisted.
   */
  public function isBlacklisted();
}
