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
interface DistributorInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface
{

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
     * Returns the Distributor active status indicator.
     *
     * Inactive Distributor are only visible to restricted users.
     *
     * @return bool
     *   TRUE if the Distributor is active.
     */
    public function isActive();

    /**
     * Sets the active status of a Distributor.
     *
     * @param bool $active
     *   TRUE to set this Distributor to active, FALSE to set it to inactive.
     *
     * @return \Drupal\distribution\Entity\DistributorInterface
     *   The called Distributor entity.
     */
    public function setActive($active);

    /**
     * @return integer
     */
    public function getLevelNumber();

    /**
     * @return Distributor|null
     */
    public function getUpstreamDistributor();

    /**
     * @param boolean $bool
     * @return Distributor
     */
    public function setIsLeader($bool);

    /**
     * @return boolean
     */
    public function isLeader();

  /**
   * @param $bool
   * @return $this
   */
  public function setSenior($bool);

  /**
   * @return bool
   */
  public function isSenior();
}
