<?php

namespace Drupal\wildlife_author\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Author entities.
 *
 * @ingroup wildlife_author
 */
interface AuthorInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Author name.
   *
   * @return string
   *   Name of the Author.
   */
  public function getName();

  /**
   * Sets the Author name.
   *
   * @param string $name
   *   The Author name.
   *
   * @return \Drupal\wildlife_author\Entity\AuthorInterface
   *   The called Author entity.
   */
  public function setName($name);

  /**
   * Gets the Author creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Author.
   */
  public function getCreatedTime();

  /**
   * Sets the Author creation timestamp.
   *
   * @param int $timestamp
   *   The Author creation timestamp.
   *
   * @return \Drupal\wildlife_author\Entity\AuthorInterface
   *   The called Author entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Author published status indicator.
   *
   * Unpublished Author are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Author is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Author.
   *
   * @param bool $published
   *   TRUE to set this Author to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\wildlife_author\Entity\AuthorInterface
   *   The called Author entity.
   */
  public function setPublished($published);

  /**
   * Gets the Author revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Author revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\wildlife_author\Entity\AuthorInterface
   *   The called Author entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Author revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Author revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\wildlife_author\Entity\AuthorInterface
   *   The called Author entity.
   */
  public function setRevisionUserId($uid);

}
