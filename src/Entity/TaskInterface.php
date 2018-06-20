<?php

namespace Drupal\distribution\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\distribution\Plugin\TaskTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Task entities.
 *
 * @ingroup distribution
 */
interface TaskInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Task name.
   *
   * @return string
   *   Name of the Task.
   */
  public function getName();

  /**
   * Sets the Task name.
   *
   * @param string $name
   *   The Task name.
   *
   * @return \Drupal\distribution\Entity\TaskInterface
   *   The called Task entity.
   */
  public function setName($name);

  /**
   * Gets the Task creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Task.
   */
  public function getCreatedTime();

  /**
   * Sets the Task creation timestamp.
   *
   * @param int $timestamp
   *   The Task creation timestamp.
   *
   * @return \Drupal\distribution\Entity\TaskInterface
   *   The called Task entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Task published status indicator.
   *
   * Unpublished Task are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Task is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Task.
   *
   * @param bool $published
   *   TRUE to set this Task to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\distribution\Entity\TaskInterface
   *   The called Task entity.
   */
  public function setPublished($published);

  /**
   * @return bool
   */
  public function isUpgrade();

  /**
   * @param $upgrade
   * @return $this
   */
  public function setUpgrade($upgrade);

  /**
   * @return TaskTypeInterface
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getBundlePlugin();

  /**
   * @return int
   */
  public function getCycle();

  /**
   * @param $days
   * @return $this
   */
  public function setCycle($days);

  /**
   * @return Price
   */
  public function getReward();

  /**
   * @param Price $amount
   * @return $this
   */
  public function setReward(Price $amount);
}
