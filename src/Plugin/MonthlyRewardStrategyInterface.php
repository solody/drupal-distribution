<?php

namespace Drupal\distribution\Plugin;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\distribution\Entity\DistributorInterface;

/**
 * Defines an interface for Monthly reward strategy plugins.
 */
interface MonthlyRewardStrategyInterface extends PluginInspectionInterface {

  /**
   * 提升一个分销订单所相关的分销会员的奖励比值
   * @param OrderInterface $order
   * @return bool
   */
  public function elevateState(OrderInterface $order);

  /**
   * 对一个分销会员执行月度奖励
   * @param DistributorInterface $distributor
   * @param array $month
   * @param Price $amount
   * @return bool
   */
  public function assignReward(DistributorInterface $distributor, array $month, Price $amount);
}
