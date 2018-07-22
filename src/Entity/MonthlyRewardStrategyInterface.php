<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Monthly reward strategy entities.
 */
interface MonthlyRewardStrategyInterface extends ConfigEntityInterface {

  /**
   * Gets the payment gateway plugin.
   *
   * @return \Drupal\distribution\Plugin\MonthlyRewardStrategyInterface
   *   The payment gateway plugin.
   */
  public function getPlugin();

  /**
   * Gets the payment gateway plugin ID.
   *
   * @return string
   *   The payment gateway plugin ID.
   */
  public function getPluginId();

  /**
   * Sets the payment gateway plugin ID.
   *
   * @param string $plugin_id
   *   The payment gateway plugin ID.
   *
   * @return $this
   */
  public function setPluginId($plugin_id);

  /**
   * Gets the payment gateway plugin configuration.
   *
   * @return array
   *   The payment gateway plugin configuration.
   */
  public function getPluginConfiguration();

  /**
   * Sets the payment gateway plugin configuration.
   *
   * @param array $configuration
   *   The payment gateway plugin configuration.
   *
   * @return $this
   */
  public function setPluginConfiguration(array $configuration);
  // Add get/set methods for your configuration properties here.
}
