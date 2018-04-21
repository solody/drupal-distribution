<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Level entities.
 *
 * @ingroup distribution
 */
interface LevelInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Level name.
   *
   * @return string
   *   Name of the Level.
   */
  public function getName();

  /**
   * Sets the Level name.
   *
   * @param string $name
   *   The Level name.
   *
   * @return \Drupal\distribution\Entity\LevelInterface
   *   The called Level entity.
   */
  public function setName($name);

  /**
   * Gets the Level creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Level.
   */
  public function getCreatedTime();

  /**
   * Sets the Level creation timestamp.
   *
   * @param int $timestamp
   *   The Level creation timestamp.
   *
   * @return \Drupal\distribution\Entity\LevelInterface
   *   The called Level entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Level published status indicator.
   *
   * Unpublished Level are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Level is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Level.
   *
   * @param bool $published
   *   TRUE to set this Level to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\LevelInterface
   *   The called Level entity.
   */
  public function setPublished($published);

}
