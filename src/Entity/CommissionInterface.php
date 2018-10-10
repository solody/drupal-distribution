<?php

namespace Drupal\distribution\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Commission entities.
 *
 * @ingroup distribution
 */
interface CommissionInterface extends ContentEntityInterface, EntityChangedInterface {

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

  public function isValid();

  public function setValid($valid);

  /**
   * @return Distributor
   */
  public function getDistributor();

  /**
   * @return EventInterface
   */
  public function getEvent();

  /**
   * @return Price
   */
  public function getAmount();
}
