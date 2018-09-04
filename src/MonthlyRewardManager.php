<?php

namespace Drupal\distribution;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\Leader;
use Drupal\distribution\Entity\MonthlyRewardCondition;
use Drupal\distribution\Entity\MonthlyRewardStrategy;
use Drupal\distribution\Entity\MonthlyStatement;
use Drupal\distribution\Entity\TargetInterface;
use Drupal\distribution\Plugin\MonthlyRewardConditionInterface;
use Drupal\distribution\Plugin\MonthlyRewardStrategyInterface;
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
   * @throws \Exception
   */
  public function handleDistribution(OrderInterface $order) {
    $this->generateRewardPoolAmount($order);
    $this->elevateCommissionConditionState($order);
    $this->elevateCommissionStrategyState($order);
  }

  /**
   * 生成月度奖励报告
   * @throws \Exception
   */
  public function generateMonthlyCommissionStatement() {
    // 检查时间，检查是否需要生成奖励报告
    $now = new \DateTime();
    $last_month = $now->sub(new \DateInterval('P1M'));
    // 检查上一个月的报告是否已经生成
    $month = [(int)$last_month->format('Y'), (int)$last_month->format('m')];
    $query = \Drupal::entityQuery('distribution_monthly_statement');
    $generated = $query
      ->condition('month', $month[0].$month[1])
      ->count()->execute();

    // 执行月度奖金分配，并生成奖励报告
    if (!$generated) {
      $statement = $this->createMonthlyStatement($month);
      $condition_plugin = $this->determineConditionPlugin();
      $strategy_plugin = $this->determineStrategyPlugin();

      $reward_assigned = new Price('0.00', 'CNY');
      $quantity_assigned = 0;

      // 查找所有分销会员，评价其是否达到了奖励条件
      /** @var Distributor[] $distributors */
      $distributors = Distributor::loadMultiple();
      foreach ($distributors as $distributor) {
        // 团队领导不能参与月度奖励
        if ($distributor->isLeader()) continue;
        if ($condition_plugin->evaluate($distributor, $month)) {
          $amount = $strategy_plugin->assignReward($distributor, $month, $statement);
          $reward_assigned = $reward_assigned->add($amount);
          $quantity_assigned++;
        }
      }

      $statement->setRewardAssigned($reward_assigned);
      $statement->setQuantityAssigned($quantity_assigned);
      $statement->save();
    }
  }

  /**
   * @param $month
   * @return \Drupal\Core\Entity\EntityInterface|MonthlyStatement
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createMonthlyStatement($month) {
    // 计算月度奖池总奖金
    $statement = MonthlyStatement::create([
      'name'=> $month[0].'年'.$month[1].'月，月度奖励报告',
      'month' => $month[0].$month[1],
      'reward_total' => $this->countRewardPool($month),
      'reward_assigned' => new Price('0.00', 'CNY'),
      'quantity_assigned' => 0
    ]);
    $statement->save();
    return $statement;
  }

  /**
   * @param $month
   * @return Price
   */
  private function countRewardPool($month) {
    $query = \Drupal::entityQuery('finance_ledger');
    $query
      ->condition('account_id', $this->getRewardPoolFinanceAccount()->id())
      ->condition('created', (new \DateTime($month[0].'-'.$month[1].'-01 00:00:00'))->getTimestamp(), '>=')
      ->condition('created', (new \DateTime($month[0].'-'.($month[1] + 1).'-01 00:00:00'))->getTimestamp(), '<');

    $ids = $query->execute();
    $amount = new Price('0.00', 'CNY');
    if (count($ids)) {
      $ledgers = Ledger::loadMultiple($ids);
      foreach ($ledgers as $ledger) {
        if ($ledger instanceof LedgerInterface) {
          if ($ledger->getAmountType() === Ledger::AMOUNT_TYPE_DEBIT) $amount = $amount->add($ledger->getAmount());
          if ($ledger->getAmountType() === Ledger::AMOUNT_TYPE_CREDIT) $amount = $amount->subtract($ledger->getAmount());
        }
      }
    }

    return $amount;
  }

  /**
   * 提升订单相关用户的奖励条件值
   * @param OrderInterface $order
   * @throws \Exception
   */
  private function elevateCommissionConditionState(OrderInterface $order) {
    // 确定当前使用的条件配置，找到配置所选的条件插件，执行插件的提升接口
    $plugin = $this->determineConditionPlugin();
    $plugin->elevateState($order);
  }

  /**
   * 提升订单相关用户的奖金分配策略比值
   * @param OrderInterface $order
   * @throws \Exception
   */
  private function elevateCommissionStrategyState(OrderInterface $order) {
    // 确定当前使用的策略配置，找到配置所选的策略插件，执行插件的提升接口
    $plugin = $this->determineStrategyPlugin();
    $plugin->elevateState($order);
  }

  /**
   * @return MonthlyRewardConditionInterface
   * @throws \Exception
   */
  private function determineConditionPlugin() {
    $config_entity_id = $this->getMonthlyRewardConfig('monthly_reward.condition');
    if ($config_entity_id) {
      $config_entity = MonthlyRewardCondition::load($config_entity_id);
      return $config_entity->getPlugin();
    } else {
      throw new \Exception('请配置奖励条件');
    }
  }

  /**
   * @return MonthlyRewardStrategyInterface
   * @throws \Exception
   */
  private function determineStrategyPlugin() {
    $config_entity_id = $this->getMonthlyRewardConfig('monthly_reward.strategy');
    if ($config_entity_id) {
      $config_entity = MonthlyRewardStrategy::load($config_entity_id);
      return $config_entity->getPlugin();
    } else {
      throw new \Exception('请配置奖励策略');
    }
  }

  private function getMonthlyRewardConfig($key) {
    $config = \Drupal::config('distribution.settings');
    return $config->get($key);
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
