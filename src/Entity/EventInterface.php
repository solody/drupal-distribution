<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event entities.
 *
 * @ingroup distribution
 */
interface EventInterface extends ContentEntityInterface, EntityChangedInterface
{

    // Add get/set methods for your configuration properties here.

    /**
     * Gets the Event name.
     *
     * @return string
     *   Name of the Event.
     */
    public function getName();

    /**
     * Sets the Event name.
     *
     * @param string $name
     *   The Event name.
     *
     * @return \Drupal\distribution\Entity\EventInterface
     *   The called Event entity.
     */
    public function setName($name);

    /**
     * Gets the Event creation timestamp.
     *
     * @return int
     *   Creation timestamp of the Event.
     */
    public function getCreatedTime();

    /**
     * Sets the Event creation timestamp.
     *
     * @param int $timestamp
     *   The Event creation timestamp.
     *
     * @return \Drupal\distribution\Entity\EventInterface
     *   The called Event entity.
     */
    public function setCreatedTime($timestamp);


    public function isValid();

    public function setValid($valid);

}
