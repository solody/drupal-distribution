<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Monthly statement entities.
 *
 * @ingroup distribution
 */
interface MonthlyStatementInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Monthly statement name.
   *
   * @return string
   *   Name of the Monthly statement.
   */
  public function getName();

  /**
   * Sets the Monthly statement name.
   *
   * @param string $name
   *   The Monthly statement name.
   *
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setName($name);

  /**
   * Gets the Monthly statement creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Monthly statement.
   */
  public function getCreatedTime();

  /**
   * Sets the Monthly statement creation timestamp.
   *
   * @param int $timestamp
   *   The Monthly statement creation timestamp.
   *
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Monthly statement published status indicator.
   *
   * Unpublished Monthly statement are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Monthly statement is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Monthly statement.
   *
   * @param bool $published
   *   TRUE to set this Monthly statement to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setPublished($published);

}
