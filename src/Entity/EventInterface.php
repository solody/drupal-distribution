<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event entities.
 *
 * @ingroup distribution
 */
interface EventInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Event name.
   *
   * @return string
   *   Name of the Event.
   */
  public function getName();

  /**
   * Sets the Event name.
   *
   * @param string $name
   *   The Event name.
   *
   * @return \Drupal\distribution\Entity\EventInterface
   *   The called Event entity.
   */
  public function setName($name);

  /**
   * Gets the Event creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event.
   */
  public function getCreatedTime();

  /**
   * Sets the Event creation timestamp.
   *
   * @param int $timestamp
   *   The Event creation timestamp.
   *
   * @return \Drupal\distribution\Entity\EventInterface
   *   The called Event entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Event published status indicator.
   *
   * Unpublished Event are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Event is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Event.
   *
   * @param bool $published
   *   TRUE to set this Event to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\EventInterface
   *   The called Event entity.
   */
  public function setPublished($published);

}
