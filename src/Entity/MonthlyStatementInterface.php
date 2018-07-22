<?php

namespace Drupal\distribution\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Monthly statement entities.
 *
 * @ingroup distribution
 */
interface MonthlyStatementInterface extends ContentEntityInterface, EntityChangedInterface {

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
   * Gets the Monthly statement month.
   *
   * @return string
   *   Month of the Monthly statement.
   */
  public function getMonth();

  /**
   * Sets the Monthly statement month.
   *
   * @param string $month
   *   The Monthly statement month.
   *
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setMonth($month);

  /**
   * @return Price|null
   */
  public function getRewardTotal();

  /**
   * @param Price $price
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setRewardTotal(Price $price);

  /**
   * @return Price|null
   */
  public function getRewardAssigned();

  /**
   * @param Price $price
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setRewardAssigned(Price $price);

  /**
   * @return integer
   */
  public function getQuantityAssigned();

  /**
   * @param integer $quantity
   * @return \Drupal\distribution\Entity\MonthlyStatementInterface
   *   The called Monthly statement entity.
   */
  public function setQuantityAssigned($quantity);
}
