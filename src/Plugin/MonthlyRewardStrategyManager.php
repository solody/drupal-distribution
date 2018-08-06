<?php

namespace Drupal\distribution\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Monthly reward strategy plugin manager.
 */
class MonthlyRewardStrategyManager extends DefaultPluginManager {


  /**
   * Constructs a new MonthlyRewardStrategyManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MonthlyRewardStrategy', $namespaces, $module_handler, 'Drupal\distribution\Plugin\MonthlyRewardStrategyInterface', 'Drupal\distribution\Annotation\MonthlyRewardStrategy');

    $this->alterInfo('distribution_monthly_reward_strategy_info');
    $this->setCacheBackend($cache_backend, 'distribution_monthly_reward_strategy_plugins');
  }

}