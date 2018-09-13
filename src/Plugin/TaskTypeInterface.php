<?php

namespace Drupal\distribution\Plugin;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\distribution\Entity\Acceptance;
use Drupal\distribution\Entity\AcceptanceInterface;
use Drupal\distribution\Entity\TaskInterface;
use Drupal\entity\BundlePlugin\BundlePluginInterface;

/**
 * Defines an interface for Task type plugins.
 */
interface TaskTypeInterface extends PluginInspectionInterface, BundlePluginInterface {

  /**
   * 计算一个订单在一个任务中可获得的分数
   * @param AcceptanceInterface $acceptance
   * @param OrderInterface $commerce_order
   * @return float
   */
  public function computeScore(AcceptanceInterface $acceptance, OrderInterface $commerce_order);

  /**
   * 检查给定分数有否完成一个任务
   * @param TaskInterface $task
   * @param $score
   * @return bool
   */
  public function canCompleted(TaskInterface $task, $score);

}
