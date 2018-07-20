<?php

namespace Drupal\distribution\Plugin;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\distribution\Entity\DistributorInterface;

/**
 * Defines an interface for Monthly reward condition plugins.
 */
interface MonthlyRewardConditionInterface extends PluginInspectionInterface {

  /**
   * 提升一个分销订单所相关的分销会员的条件值
   * @param OrderInterface $order
   * @return bool
   */
  public function elevateState(OrderInterface $order);

  /**
   * 评估一个分销会员，是否达到月度奖励条件
   * @param DistributorInterface $distributor
   * @param array $month
   * @return bool
   */
  public function evaluate(DistributorInterface $distributor, array $month);
}
