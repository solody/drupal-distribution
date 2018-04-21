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
interface PromoterInterface extends ContentEntityInterface, EntityChangedInterface
{

    // Add get/set methods for your configuration properties here.


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


}
