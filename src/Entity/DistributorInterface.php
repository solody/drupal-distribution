<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Distributor entities.
 *
 * @ingroup distribution
 */
interface DistributorInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Distributor name.
   *
   * @return string
   *   Name of the Distributor.
   */
  public function getName();

  /**
   * Sets the Distributor name.
   *
   * @param string $name
   *   The Distributor name.
   *
   * @return \Drupal\distribution\Entity\DistributorInterface
   *   The called Distributor entity.
   */
  public function setName($name);

  /**
   * Gets the Distributor creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Distributor.
   */
  public function getCreatedTime();

  /**
   * Sets the Distributor creation timestamp.
   *
   * @param int $timestamp
   *   The Distributor creation timestamp.
   *
   * @return \Drupal\distribution\Entity\DistributorInterface
   *   The called Distributor entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Distributor published status indicator.
   *
   * Unpublished Distributor are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Distributor is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Distributor.
   *
   * @param bool $published
   *   TRUE to set this Distributor to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\DistributorInterface
   *   The called Distributor entity.
   */
  public function setPublished($published);

}
