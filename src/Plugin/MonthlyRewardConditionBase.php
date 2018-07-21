<?php

namespace Drupal\distribution\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PluginWithFormsTrait;

/**
 * Base class for Monthly reward condition plugins.
 */
abstract class MonthlyRewardConditionBase extends PluginBase implements PluginWithFormsInterface, ConfigurablePluginInterface, PluginFormInterface, MonthlyRewardConditionInterface {


  // Add common methods and abstract methods for your plugin type here.
  use PluginWithFormsTrait;

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }
}
