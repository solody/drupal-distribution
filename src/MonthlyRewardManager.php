<?php

namespace Drupal\distribution;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\distribution\Entity\Leader;
use Drupal\distribution\Entity\TargetInterface;
use Drupal\finance\Entity\Ledger;
use Drupal\finance\Entity\LedgerInterface;
use Drupal\finance\FinanceManagerInterface;
use Drupal\user\Entity\User;

/**
 * Class MonthlyRewardManager.
 */
class MonthlyRewardManager implements MonthlyRewardManagerInterface {

  /**
   * Constructs a new MonthlyRewardManager object.
   */
  public function __construct() {

  }

  /**
   * 处理新的分销订单，添加奖金池金额、更新奖励条件值、更新奖金分配策略比值等
   * @param OrderInterface $order
   * @return mixed
   */
  public function handleDistribution(OrderInterface $order) {
    $this->generateRewardPoolAmount($order);
    $this->elevateCommissionConditionState($order);
    $this->elevateCommissionStrategyState($order);
  }

  /**
   * 生成月度奖励报告
   */
  public function generateMonthlyCommissionStatement() {
    // 检查时间，检查是否需要生成奖励报告
    // 执行月度奖金分配，并生成奖励报告
  }

  /**
   * 提升订单相关用户的奖励条件值
   * @param OrderInterface $order
   */
  private function elevateCommissionConditionState(OrderInterface $order) {
    // 确定当前使用的条件配置，找到配置所选的条件插件，执行插件的提升接口
  }

  /**
   * 提升订单相关用户的奖金分配策略比值
   * @param OrderInterface $order
   */
  private function elevateCommissionStrategyState(OrderInterface $order) {
    // 确定当前使用的策略配置，找到配置所选的策略插件，执行插件的提升接口
  }

  /**
   * 添加奖金池金额
   * @param OrderInterface $order
   */
  private function generateRewardPoolAmount(OrderInterface $order) {
    // TODO::防止重复处理
    // $this->getFinanceManager()->getLedgers($this->getRewardPoolFinanceAccount());

    foreach ($order->getItems() as $orderItem) {
      $target = $this->getDistributionManager()->getTarget($orderItem->getPurchasedEntity());
      if ($target instanceof TargetInterface) {
        $amount = null;
        if ($this->getDistributionConfig()->get('commission.compute_mode') === 'dynamic_percentage') {
          if ($target->getPercentageMonthlyReward() > 0) {
            $amount = $orderItem->getPurchasedEntity()->getPrice()
              ->multiply((string)$target->getPercentageMonthlyReward())
              ->multiply('0.01');
          }
        } elseif ($this->getDistributionConfig()->get('commission.compute_mode') === 'fixed_amount') {
          $amount = $target->getAmountMonthlyReward();
        }

        if ($amount instanceof Price && !$amount->isZero()) {
          // 记账到奖金池
          $this->getFinanceManager()
            ->createLedger($this->getRewardPoolFinanceAccount(),
              Ledger::AMOUNT_TYPE_DEBIT,
              $amount->multiply($orderItem->getQuantity()),
              '订单['.$order->id().']中的商品['.$orderItem->getTitle().']产生了月度奖金：'.
              $amount->getCurrencyCode().$amount->getNumber().' x '.$orderItem->getQuantity(),
              $order);
        }
      }
    }
  }

  /**
   * 如果没有奖金池账户，创建一个
   */
  private function getRewardPoolFinanceAccount() {
    $accounts = $this->getFinanceManager()->getAccountsByType('distribution_monthly_reward_pool');
    if (count($accounts)) {
      return array_pop($accounts);
    } else {
      // 奖金池账户
      return $this->getFinanceManager()->createAccount(User::load(1), 'distribution_monthly_reward_pool');
    }
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

  /**
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  private function getDistributionConfig() {
    return \Drupal::config('distribution.settings');
  }
}
