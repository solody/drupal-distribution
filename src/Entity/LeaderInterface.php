<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Leader entities.
 *
 * @ingroup distribution
 */
interface LeaderInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Leader name.
   *
   * @return string
   *   Name of the Leader.
   */
  public function getName();

  /**
   * Sets the Leader name.
   *
   * @param string $name
   *   The Leader name.
   *
   * @return \Drupal\distribution\Entity\LeaderInterface
   *   The called Leader entity.
   */
  public function setName($name);

  /**
   * Gets the Leader creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Leader.
   */
  public function getCreatedTime();

  /**
   * Sets the Leader creation timestamp.
   *
   * @param int $timestamp
   *   The Leader creation timestamp.
   *
   * @return \Drupal\distribution\Entity\LeaderInterface
   *   The called Leader entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Leader published status indicator.
   *
   * Unpublished Leader are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Leader is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Leader.
   *
   * @param bool $published
   *   TRUE to set this Leader to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\LeaderInterface
   *   The called Leader entity.
   */
  public function setPublished($published);

}
