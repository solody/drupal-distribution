<?php

namespace Drupal\distribution\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PluginWithFormsTrait;
use Drupal\distribution\DistributionManagerInterface;
use Drupal\finance\FinanceManagerInterface;

/**
 * Base class for Monthly reward strategy plugins.
 */
abstract class MonthlyRewardStrategyBase extends PluginBase implements PluginWithFormsInterface, ConfigurablePluginInterface, PluginFormInterface, MonthlyRewardStrategyInterface {

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

  protected function createCommission() {
  }

  /**
   * @return FinanceManagerInterface
   */
  private function getFinanceManager() {
    return \Drupal::getContainer()->get('finance.finance_manager');
  }

  /**
   * @return DistributionManagerInterface
   */
  private function getDistributionManager() {
    return \Drupal::getContainer()->get('distribution.distribution_manager');
  }
}
