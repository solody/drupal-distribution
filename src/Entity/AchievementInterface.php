<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Achievement entities.
 *
 * @ingroup distribution
 */
interface AchievementInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the Achievement creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Achievement.
   */
  public function getCreatedTime();

  /**
   * Sets the Achievement creation timestamp.
   *
   * @param int $timestamp
   *   The Achievement creation timestamp.
   *
   * @return \Drupal\distribution\Entity\AchievementInterface
   *   The called Achievement entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Achievement valid status indicator.
   *
   * Unvalid Achievement are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Achievement is valid.
   */
  public function isValid();

  /**
   * Sets the valid status of a Achievement.
   *
   * @param bool $valid
   *   TRUE to set this Achievement to valid, FALSE to set it to unvalid.
   *
   * @return \Drupal\distribution\Entity\AchievementInterface
   *   The called Achievement entity.
   */
  public function setValid($valid);

  /**
   * @return float
   */
  public function getScore();

  /**
   * @param $score
   * @return $this
   */
  public function setScore($score);
}
