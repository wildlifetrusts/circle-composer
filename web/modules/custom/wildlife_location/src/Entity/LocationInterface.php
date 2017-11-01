<?php

namespace Drupal\wildlife_location\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Location entities.
 *
 * @ingroup wildlife_location
 */
interface LocationInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Location name.
   *
   * @return string
   *   Name of the Location.
   */
  public function getName();

  /**
   * Sets the Location name.
   *
   * @param string $name
   *   The Location name.
   *
   * @return \Drupal\wildlife_location\Entity\LocationInterface
   *   The called Location entity.
   */
  public function setName($name);

  /**
   * Gets the Location creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Location.
   */
  public function getCreatedTime();

  /**
   * Sets the Location creation timestamp.
   *
   * @param int $timestamp
   *   The Location creation timestamp.
   *
   * @return \Drupal\wildlife_location\Entity\LocationInterface
   *   The called Location entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Location published status indicator.
   *
   * Unpublished Location are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Location is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Location.
   *
   * @param bool $published
   *   TRUE to set this Location to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\wildlife_location\Entity\LocationInterface
   *   The called Location entity.
   */
  public function setPublished($published);

  /**
   * Gets the Location revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Location revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\wildlife_location\Entity\LocationInterface
   *   The called Location entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Location revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Location revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\wildlife_location\Entity\LocationInterface
   *   The called Location entity.
   */
  public function setRevisionUserId($uid);

}
