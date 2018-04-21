<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Promoter entities.
 *
 * @ingroup distribution
 */
interface PromoterInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Promoter name.
   *
   * @return string
   *   Name of the Promoter.
   */
  public function getName();

  /**
   * Sets the Promoter name.
   *
   * @param string $name
   *   The Promoter name.
   *
   * @return \Drupal\distribution\Entity\PromoterInterface
   *   The called Promoter entity.
   */
  public function setName($name);

  /**
   * Gets the Promoter creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Promoter.
   */
  public function getCreatedTime();

  /**
   * Sets the Promoter creation timestamp.
   *
   * @param int $timestamp
   *   The Promoter creation timestamp.
   *
   * @return \Drupal\distribution\Entity\PromoterInterface
   *   The called Promoter entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Promoter published status indicator.
   *
   * Unpublished Promoter are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Promoter is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Promoter.
   *
   * @param bool $published
   *   TRUE to set this Promoter to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\PromoterInterface
   *   The called Promoter entity.
   */
  public function setPublished($published);

}
