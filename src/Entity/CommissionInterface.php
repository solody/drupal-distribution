<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Commission entities.
 *
 * @ingroup distribution
 */
interface CommissionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Commission name.
   *
   * @return string
   *   Name of the Commission.
   */
  public function getName();

  /**
   * Sets the Commission name.
   *
   * @param string $name
   *   The Commission name.
   *
   * @return \Drupal\distribution\Entity\CommissionInterface
   *   The called Commission entity.
   */
  public function setName($name);

  /**
   * Gets the Commission creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Commission.
   */
  public function getCreatedTime();

  /**
   * Sets the Commission creation timestamp.
   *
   * @param int $timestamp
   *   The Commission creation timestamp.
   *
   * @return \Drupal\distribution\Entity\CommissionInterface
   *   The called Commission entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Commission published status indicator.
   *
   * Unpublished Commission are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Commission is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Commission.
   *
   * @param bool $published
   *   TRUE to set this Commission to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\CommissionInterface
   *   The called Commission entity.
   */
  public function setPublished($published);

}
