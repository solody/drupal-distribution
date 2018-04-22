<?php

namespace Drupal\distribution\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Target entities.
 *
 * @ingroup distribution
 */
interface TargetInterface extends ContentEntityInterface, EntityChangedInterface
{

    // Add get/set methods for your configuration properties here.

    /**
     * Gets the Target name.
     *
     * @return string
     *   Name of the Target.
     */
    public function getName();

    /**
     * Sets the Target name.
     *
     * @param string $name
     *   The Target name.
     *
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setName($name);

    /**
     * Gets the Target creation timestamp.
     *
     * @return int
     *   Creation timestamp of the Target.
     */
    public function getCreatedTime();

    /**
     * Sets the Target creation timestamp.
     *
     * @param int $timestamp
     *   The Target creation timestamp.
     *
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setCreatedTime($timestamp);

    /**
     * @return bool
     *   TRUE if the Target is active.
     */
    public function isActive();

    /**
     * Sets the active status of a Target.
     *
     * @param bool $active
     *   TRUE to set this Distributor to active, FALSE to set it to inactive.
     *
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setActive($active);

    /**
     * @return Price|null
     */
    public function getAmountOff();

    /**
     * @param Price $price
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setAmountOff(Price $price);

    /**
     * @return Price|null
     */
    public function getAmountPromotion();

    /**
     * @param Price $price
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setAmountPromotion(Price $price);

    /**
     * @return Price|null
     */
    public function getAmountChain();

    /**
     * @param Price $price
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setAmountChain(Price $price);

    /**
     * @return Price|null
     */
    public function getAmountLeader();

    /**
     * @param Price $price
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setAmountLeader(Price $price);

    /**
     * @return float|null
     */
    public function getPercentagePromotion();

    /**
     * @param $value
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setPercentagePromotion($value);

    /**
     * @return float|null
     */
    public function getPercentageChain();

    /**
     * @param $value
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setPercentageChain($value);

    /**
     * @return float|null
     */
    public function getPercentageLeader();

    /**
     * @param $value
     * @return \Drupal\distribution\Entity\TargetInterface
     *   The called Target entity.
     */
    public function setPercentageLeader($value);
}
