<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Acceptance entities.
 *
 * @ingroup distribution
 */
interface AcceptanceInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  /**
   * Gets the Acceptance creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Acceptance.
   */
  public function getCreatedTime();

  /**
   * Sets the Acceptance creation timestamp.
   *
   * @param int $timestamp
   *   The Acceptance creation timestamp.
   *
   * @return \Drupal\distribution\Entity\AcceptanceInterface
   *   The called Acceptance entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Acceptance completed status indicator.
   *
   * Uncompleted Acceptance are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Acceptance is completed.
   */
  public function isCompleted();

  /**
   * Sets the completed status of a Acceptance.
   *
   * @param bool $completed
   *   TRUE to set this Acceptance to completed, FALSE to set it to uncompleted.
   *
   * @return \Drupal\distribution\Entity\AcceptanceInterface
   *   The called Acceptance entity.
   */
  public function setCompleted($completed);

  /**
   * @return int
   */
  public function getTaskId();

  /**
   * @return TaskInterface
   */
  public function getTask();

  /**
   * @param TaskInterface $task
   * @return $this
   */
  public function setTask(TaskInterface $task);
}
