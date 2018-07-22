<?php

namespace Drupal\distribution;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Interface MonthlyRewardManagerInterface.
 */
interface MonthlyRewardManagerInterface {
  /**
   * 处理新的分销订单，添加奖金池金额、更新奖励条件值、更新奖金分配策略比值等
   * @param OrderInterface $order
   * @return mixed
   */
  public function handleDistribution(OrderInterface $order);

  /**
   * @return mixed
   */
  public function generateMonthlyCommissionStatement();
}
