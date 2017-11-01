<?php

namespace Drupal\wildlife_view_headers\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining View header entities.
 *
 * @ingroup wildlife_view_headers
 */
interface ViewHeaderInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {
  /**
   * Gets the View header name.
   *
   * @return string
   *   Name of the View header.
   */
  public function getName();

  /**
   * Sets the View header name.
   *
   * @param string $name
   *   The View header name.
   *
   * @return \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface
   *   The called View header entity.
   */
  public function setName($name);

  /**
   * Gets the View header creation timestamp.
   *
   * @return int
   *   Creation timestamp of the View header.
   */
  public function getCreatedTime();

  /**
   * Sets the View header creation timestamp.
   *
   * @param int $timestamp
   *   The View header creation timestamp.
   *
   * @return \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface
   *   The called View header entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the View header published status indicator.
   *
   * Unpublished View header are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the View header is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a View header.
   *
   * @param bool $published
   *   TRUE to set this View header to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface
   *   The called View header entity.
   */
  public function setPublished($published);

  /**
   * Gets the View header revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the View header revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface
   *   The called View header entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the View header revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the View header revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\wildlife_view_headers\Entity\ViewHeaderInterface
   *   The called View header entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Get the view id associated with a View Header entity.
   *
   * @return string
   */
  public function getAssociatedViewId();

  /**
   * Get the view display id associated with a View Header entity.
   *
   * @return string
   */
  public function getAssociatedViewDisplayId();
}
