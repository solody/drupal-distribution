<?php

namespace Drupal\distribution;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\Core\Session\AccountInterface;
use Drupal\distribution\Entity\AcceptanceInterface;
use Drupal\distribution\Entity\Leader;
use Drupal\distribution\Entity\LeaderInterface;
use Drupal\distribution\Entity\PromoterInterface;
use Drupal\distribution\Event\CommissionEvent;
use Drupal\finance\Entity\Ledger;
use Drupal\finance\FinanceManagerInterface;
use Drupal\distribution\Entity\Commission;
use Drupal\distribution\Entity\Promoter;
use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\Event;
use Drupal\distribution\Entity\Target;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\user\Entity\User;

/**
 * Class DistributionManager.
 */
class DistributionManager implements DistributionManagerInterface {
  const FINANCE_ACCOUNT_TYPE = 'distribution';
  const FINANCE_PENDING_ACCOUNT_TYPE = 'distribution_pending';
  /**
   * Drupal\finance\FinanceManagerInterface definition.
   *
   * @var \Drupal\finance\FinanceManagerInterface
   */
  protected $financeFinanceManager;

  /**
   * @var TaskManagerInterface
   */
  protected $taskManager;

  /**
   * Constructs a new DistributionManager object.
   * @param FinanceManagerInterface $finance_finance_manager
   * @param TaskManagerInterface $task_manager
   */
  public function __construct(FinanceManagerInterface $finance_finance_manager, TaskManagerInterface $task_manager) {
    $this->financeFinanceManager = $finance_finance_manager;
    $this->taskManager = $task_manager;
  }

  /**
   * @return \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  private function getEventDispatcher() {
    return \Drupal::getContainer()->get('event_dispatcher');
  }

  /**
   * @inheritdoc
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function distribute(OrderInterface $commerce_order) {
    // 检查系统是否开启分销
    $config = \Drupal::config('distribution.settings');

    if ($config->get('enable')) {
      // 检查订单是否已经处理过佣金，防止重复处理
      if ($this->isDistributed($commerce_order)) return;

      // 检查订单能否确定上级分销用户
      $distributor = $this->determineDistributor($commerce_order);
      if (!$distributor) return;

      // 把分销商用户记录到订单字段
      $order = Order::load($commerce_order->id());
      $order->set('distributor', $distributor);
      $order->save();

      foreach ($commerce_order->getItems() as $orderItem) {
        $this->createEvent($orderItem, $distributor);
      }
    }
  }

  public function isDistributed(OrderInterface $commerce_order) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_event')
      ->condition('order_id', $commerce_order->id());
    $ids = $query->execute();

    return !empty($ids);
  }

  /**
   * @param OrderItemInterface $commerce_order_item
   * @param Distributor $distributor
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createEvent(OrderItemInterface $commerce_order_item, Distributor $distributor) {
    $target = $this->getTarget($commerce_order_item->getPurchasedEntity());

    // 如果商品没有设置分成，中止分佣
    if (!$target) return;

    $event = Event::create([
      'order_id' => $commerce_order_item->getOrderId(),
      'order_item_id' => $commerce_order_item->id(),
      'distributor_id' => $distributor,
      'target_id' => $target,
      'amount' => $commerce_order_item->getTotalPrice(),
      'amount_promotion' => $this->computeCommissionAmount($target, Commission::TYPE_PROMOTION, $commerce_order_item->getTotalPrice()),
      'amount_chain' => $this->computeCommissionAmount($target, Commission::TYPE_CHAIN, $commerce_order_item->getTotalPrice()),
      'amount_chain_senior' => $this->computeCommissionAmount($target, Commission::TYPE_CHAIN, $commerce_order_item->getTotalPrice(), true),
      'amount_leader' => $this->computeCommissionAmount($target, Commission::TYPE_LEADER, $commerce_order_item->getTotalPrice()),
      'name' => '订单[' . $commerce_order_item->getOrderId() . ']中商品[' . $target->getName() . ']产生佣金事件'
    ]);

    $event->save();

    $this->createCommissions($event);
  }

  /**
   * 计算佣金事件产生的特定类型的佣金总额
   *
   * @param Target $target
   * @param $commission_type
   * @param Price $price
   * @param bool $senior
   * @return Price
   */
  public function computeCommissionAmount(Target $target, $commission_type, Price $price, $senior = false) {
    // 检查配置的计算模式
    $config = \Drupal::config('distribution.settings');

    $computed_price = new Price('0.00', $price->getCurrencyCode());

    if ($config->get('commission.compute_mode') === 'fixed_amount') {
      // 固定金额，直接取已设置的固定金额
      switch ($commission_type) {
        case Commission::TYPE_PROMOTION:
          if ($target->getAmountPromotion())
            $computed_price = $computed_price->add($target->getAmountPromotion());
          break;
        case Commission::TYPE_CHAIN:
          if ($target->getAmountChain() && !$senior) {
            $computed_price = $computed_price->add($target->getAmountChain());
          }
          if ($target->getAmountChainSenior() && $senior) {
            $computed_price = $computed_price->add($target->getAmountChainSenior());
          }
          break;
        case Commission::TYPE_LEADER:
          if ($target->getAmountLeader())
            $computed_price = $computed_price->add($target->getAmountLeader());
          break;
      }
    } elseif ($config->get('commission.compute_mode') === 'dynamic_percentage') {
      // 动态计算，取百分比设置，从成交金额中计算
      $percentage = 0;
      switch ($commission_type) {
        case Commission::TYPE_PROMOTION:
          if ($target->getPercentagePromotion()) {
            $percentage = $target->getPercentagePromotion();
          }

          // 检查配置，推广佣金是否从链级佣金中计算
          if ($config->get('commission.promotion_is_part_of_chain')) {
            $chain_percentage = $target->getPercentageChain() ? $target->getPercentageChain() : 0;
            $chain_price = new Price((string)($price->getNumber() * $chain_percentage / 100), $price->getCurrencyCode());

            $computed_price = $computed_price->add(new Price((string)($chain_price->getNumber() * $percentage / 100), $chain_price->getCurrencyCode()));
          } else {
            $computed_price = $computed_price->add(new Price((string)($price->getNumber() * $percentage / 100), $price->getCurrencyCode()));
          }
          break;
        case Commission::TYPE_CHAIN:
          if ($target->getPercentageChain() && !$senior) {
            $percentage = $target->getPercentageChain();
          }
          if ($target->getPercentageChainSenior() && $senior) {
            $percentage = $target->getPercentageChainSenior();
          }
          $computed_price = $computed_price->add(new Price((string)($price->getNumber() * $percentage / 100), $price->getCurrencyCode()));
          break;
        case Commission::TYPE_LEADER:
          if ($target->getPercentageLeader()) {
            $percentage = $target->getPercentageLeader();
          }
          $computed_price = $computed_price->add(new Price((string)($price->getNumber() * $percentage / 100), $price->getCurrencyCode()));
          break;
      }
    }

    return $computed_price;
  }

  public function createCommissions(Event $distributionEvent) {
    // 检查需要产生的佣金类型
    $config = \Drupal::config('distribution.settings');

    // 推广佣金
    if ($config->get('commission.promotion')) {
      // 非分销用户成交的订单，才能产生推广佣金
      if (!$this->getDistributor($distributionEvent->getOrder()->getCustomer())) {
        // 读取推广者
        $promoters = $this->getPromoters($distributionEvent->getOrder()->getCustomer());
        // 平分佣金
        $amount = new Price((string)($distributionEvent->getAmountPromotion()->getNumber() / count($promoters)), $distributionEvent->getAmountPromotion()->getCurrencyCode());

        foreach ($promoters as $promoter) {
          $commission = Commission::create([
            'event_id' => $distributionEvent->id(),
            'type' => 'promotion',
            'distributor_id' => $promoter->getDistributor()->id(),
            'name' => $distributionEvent->getName() . '：推广佣金 ' . $distributionEvent->getAmountPromotion()->getCurrencyCode() . $distributionEvent->getAmountPromotion()->getNumber() . ' / ' . count($promoters),
            'amount' => $amount,
            'promoter_id' => $promoter->id()
          ]);
          $commission->save();

          // 记账到 Finance
          $finance_account = $this->financeFinanceManager->getAccount($promoter->getDistributor()->getOwner(), self::FINANCE_PENDING_ACCOUNT_TYPE);
          if ($finance_account) {
            $this->financeFinanceManager->createLedger(
              $finance_account,
              Ledger::AMOUNT_TYPE_DEBIT,
              $amount,
              $commission->getName(),
              $commission
            );
          }

          // 触发事件
          $this->getEventDispatcher()->dispatch(CommissionEvent::PROMOTION, new CommissionEvent($commission));
        }
      }
    }

    // 链级佣金
    if ($config->get('commission.chain')) {
      // 计算分佣链级
      $chain_commission_levels = $this->computeChainCommissionLevels($distributionEvent->getDistributor(), $distributionEvent->getAmountChain());

      foreach ($chain_commission_levels as $chain_commission_level) {
        // 如果订单购买者，是1级佣金获得者，则跳过分佣，因为他已在下单时通过价格调整的方式享受了佣金
        /** @var Distributor $distribution */
        $distribution = $chain_commission_level['distributor'];
        if ($distributionEvent->getOrder()->getCustomer()->id() === $distribution->getOwnerId()) continue;

        $commission = Commission::create([
          'event_id' => $distributionEvent->id(),
          'type' => 'chain',
          'distributor_id' => $distribution->id(),
          'name' => $distributionEvent->getName() . '：链级佣金 ' . $chain_commission_level['remark'],
          'amount' => $chain_commission_level['amount']
        ]);
        $commission->save();

        // 记账到 Finance
        $finance_account = $this->financeFinanceManager->getAccount($distribution->getOwner(), self::FINANCE_PENDING_ACCOUNT_TYPE);
        if ($finance_account) {
          $this->financeFinanceManager->createLedger(
            $finance_account,
            Ledger::AMOUNT_TYPE_DEBIT,
            $chain_commission_level['amount'],
            $commission->getName(),
            $commission
          );
        }

        // 触发事件
        $this->getEventDispatcher()->dispatch(CommissionEvent::CHAIN, new CommissionEvent($commission));
      }
    }

    // 团队领导佣金
    if ($config->get('commission.leader')) {
      // 查找团队领导
      $leader = self::computeLeader($distributionEvent->getDistributor());

      if ($leader) {
        $commission = Commission::create([
          'event_id' => $distributionEvent->id(),
          'type' => 'leader',
          'distributor_id' => $leader->getDistributor()->id(),
          'name' => $distributionEvent->getName() . '：团队领导佣金 ' . $distributionEvent->getAmountLeader()->getCurrencyCode() . $distributionEvent->getAmountLeader()->getNumber(),
          'amount' => $distributionEvent->getAmountLeader(),
          'leader_id' => $leader->id()
        ]);
        $commission->save();

        // 记账到 Finance
        $finance_account = $this->financeFinanceManager->getAccount($leader->getDistributor()->getOwner(), self::FINANCE_PENDING_ACCOUNT_TYPE);
        if ($finance_account) {
          $this->financeFinanceManager->createLedger(
            $finance_account,
            Ledger::AMOUNT_TYPE_DEBIT,
            $distributionEvent->getAmountLeader(),
            $commission->getName(),
            $commission
          );
        }

        // 触发事件
        $this->getEventDispatcher()->dispatch(CommissionEvent::LEADER, new CommissionEvent($commission));
      }
    }
  }

  /**
   * 创建任务奖励
   * @param AcceptanceInterface $acceptance
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createTaskCommissions(AcceptanceInterface $acceptance) {
    $commission = Commission::create([
      'type' => Commission::TYPE_TASK,
      'distributor_id' => $acceptance->getDistributor()->id(),
      'name' => $acceptance->getDistributor()->getName() . '完成了任务['.$acceptance->getTask()->getName().']，获得奖励' . $acceptance->getTask()->getReward()->getCurrencyCode() . $acceptance->getTask()->getReward()->getNumber(),
      'amount' => $acceptance->getTask()->getReward(),
      'acceptance_id' => $acceptance->id()
    ]);
    $commission->save();

    // 记账到 Finance
    $finance_account = $this->financeFinanceManager->getAccount($acceptance->getDistributor()->getOwner(), self::FINANCE_ACCOUNT_TYPE);
    if ($finance_account) {
      $this->financeFinanceManager->createLedger(
        $finance_account,
        Ledger::AMOUNT_TYPE_DEBIT,
        $acceptance->getTask()->getReward(),
        $commission->getName(),
        $commission
      );
    }

    // 触发事件
    $this->getEventDispatcher()->dispatch(CommissionEvent::TASK, new CommissionEvent($commission));
  }

  /**
   * @param Distributor $distributor
   * @return LeaderInterface|null|static
   */
  public static function computeLeader(Distributor $distributor) {
    $leader = null;
    $upstream_distributor = $distributor->getUpstreamDistributor();

    while (!$leader && $upstream_distributor) {
      $leader = self::getLeader($upstream_distributor);
      $upstream_distributor = $upstream_distributor->getUpstreamDistributor();
    }

    return $leader;
  }

  /**
   * @param Distributor $distributor
   * @return LeaderInterface|null|static
   */
  public static function getLeader(Distributor $distributor) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_leader')
      ->condition('distributor_id', $distributor->id());
    $ids = $query->execute();

    $leader = null;
    if (count($ids)) {
      $leader = Leader::load(array_pop($ids));
    }

    return $leader;
  }

  public function computeChainCommissionLevels(Distributor $distributor, Price $amount) {
    $setting = \Drupal::config('distribution.settings');

    $levels = [];
    for ($i = 1; $i < 4; $i++) {
      $commission_distributor = self::getUpstreamDistributor($distributor, $i);
      if (!$commission_distributor) break;

      $percentage = (float)$setting->get('chain_commission.level_' . $i);
      $commission_amount = new Price((string)($amount->getNumber() * ($percentage / 100)), $amount->getCurrencyCode());

      $levels[$i] = [
        'distributor' => $commission_distributor,
        'percentage' => $percentage,
        'amount' => $commission_amount,
        'remark' => $i . '级分佣，' . $percentage . '% x ' . $amount
      ];

      $amount = $amount->subtract($commission_amount);
    }

    return $levels;
  }

  public static function getUpstreamDistributor(Distributor $distributor, $level = 1) {
    $level = (int)$level;
    if ($level === 1) {
      return $distributor;
    } elseif ($level > 1) {
      $upstreamDistributor = $distributor->getUpstreamDistributor();

      if ($upstreamDistributor) {
        return self::getUpstreamDistributor($upstreamDistributor, $level - 1);
      }
    }
  }

  /**
   * @inheritdoc
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setTarget(PurchasableEntityInterface $purchasableEntity, $data) {
    $target = $this->getTarget($purchasableEntity);

    if (!$target) {
      $target = Target::create([
        'purchasable_entity' => $purchasableEntity
      ]);
    }

    if (isset($data['name'])) $target->setName($data['name']);

    if (isset($data['amount_off'])) $target->setAmountOff(self::makePrice($data['amount_off']));

    if (isset($data['amount_promotion'])) $target->setAmountPromotion(self::makePrice($data['amount_promotion']));
    if (isset($data['amount_chain'])) $target->setAmountChain(self::makePrice($data['amount_chain']));
    if (isset($data['amount_chain_senior'])) $target->setAmountChainSenior(self::makePrice($data['amount_chain_senior']));
    if (isset($data['amount_leader'])) $target->setAmountLeader(self::makePrice($data['amount_leader']));

    if (isset($data['percentage_promotion'])) $target->setPercentagePromotion($data['percentage_promotion']);
    if (isset($data['percentage_chain'])) $target->setPercentageChain($data['percentage_chain']);
    if (isset($data['percentage_chain_senior'])) $target->setPercentageChainSenior($data['percentage_chain_senior']);
    if (isset($data['percentage_leader'])) $target->setPercentageLeader($data['percentage_leader']);

    $target->save();

    return $target;
  }

  public static function makePrice($value) {
    return new Price((string)$value['number'], $value['currency_code']);
  }

  /**
   * @inheritdoc
   */
  public function getTarget(PurchasableEntityInterface $purchasableEntity) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_target')
      ->condition('purchasable_entity.target_id', $purchasableEntity->id())
      ->condition('purchasable_entity.target_type', $purchasableEntity->getEntityTypeId());
    $ids = $query->execute();

    $target = null;
    if (count($ids)) {
      $target = Target::load(array_pop($ids));
    }

    return $target;
  }

  /**
   * @inheritdoc
   */
  public function determineDistributor(OrderInterface $commerce_order) {
    // 检查购买者本身是不是分销用户
    $distributor = $this->getDistributor($commerce_order->getCustomer());

    if (!$distributor) {
      // 从推广关系中确定分销用户，取最后一个推广者
      /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
      $query = \Drupal::entityQuery('distribution_promoter')
        ->condition('user_id', $commerce_order->getCustomerId())
        ->sort('id', 'DESC');
      $ids = $query->execute();

      if (count($ids)) {
        $distributor = Promoter::load(array_pop($ids))->getDistributor();
      }
    }

    return $distributor;
  }

  /**
   * @inheritdoc
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createPromoter(Distributor $distributor, AccountInterface $user) {
    if ($user->isAnonymous()) {
      throw new \Exception('匿名用户不能被推广');
    }

    if ($distributor->getOwnerId() === $user->id()) {
      throw new \Exception('不能推广自己');
    }

    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_promoter')
      ->condition('distributor_id', $distributor->id())
      ->condition('user_id', $user->id());
    $ids = $query->execute();

    $promoter = null;
    if (count($ids) === 0) {
      $promoter = Promoter::create([
        'distributor_id' => $distributor->id(),
        'user_id' => $user->id()
      ]);
    } else {
      $promoter = Promoter::load(array_pop($ids));
      $promoter->setChangedTime(time());
    }

    $promoter->save();
    return $promoter;
  }

  /**
   * @param AccountInterface $user
   * @return PromoterInterface[]|static[]
   */
  public function getPromoters(AccountInterface $user) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_promoter')
      ->condition('user_id', $user->id());

    $ids = $query->execute();

    if (count($ids)) {
      return Promoter::loadMultiple($ids);
    }
  }

  /**
   * @param Distributor $distributor
   * @return \Drupal\Core\Entity\EntityInterface[]|Promoter[]
   */
  public function getPromotedUsers(Distributor $distributor) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_promoter')
      ->condition('distributor_id', $distributor->id());

    $ids = $query->execute();

    if (count($ids)) {
      return Promoter::loadMultiple($ids);
    }
  }

  /**
   * @param AccountInterface $user
   * @return Promoter|null
   */
  public function getLastPromoter(AccountInterface $user) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_promoter')
      ->condition('user_id', $user->id())
      ->sort('changed', 'DESC');

    $ids = $query->execute();

    if (count($ids)) {
      return Promoter::load(array_pop($ids));
    } else {
      return null;
    }
  }

  /**
   * @inheritdoc
   */
  public function createDistributor(AccountInterface $user, Distributor $upstream_distributor = null, $state = 'draft', $agent = []) {
    $distributor = $this->getDistributor($user);

    if (!$distributor) {
      $level_number = 1;
      if ($upstream_distributor) $level_number += $upstream_distributor->getLevelNumber();

      $price = new Price('0.00', 'CNY');

      $data = [
        'user_id' => $user->id(),
        'name' => $user->getAccountName(),
        'state' => $state,
        'level_number' => $level_number,
        'amount_achievement' => $price,
        'amount_leader' => $price,
        'amount_chain' => $price,
        'amount_promotion' => $price
      ];

      if ($upstream_distributor) $data['upstream_distributor_id'] = $upstream_distributor;
      if (isset($agent['name'])) $data['agent_name'] = $agent['name'];
      if (isset($agent['phone'])) $data['agent_phone'] = $agent['phone'];

      $distributor = Distributor::create($data);
      $distributor->save();

      /** @var User $userEntity */
      $userEntity = $user;
      $userEntity->addRole(DISTRIBUTOR_ROLE_ID);
      $userEntity->save();

      // 创建佣金管理账户（调用Finance模块）
      $this->financeFinanceManager->createAccount($user, self::FINANCE_ACCOUNT_TYPE);
      $this->financeFinanceManager->createAccount($user, self::FINANCE_PENDING_ACCOUNT_TYPE);

      // TODO:: 自动领取新手任务
      $this->taskManager->acceptNewcomerTasks($distributor);
    }

    return $distributor;
  }

  /**
   * @inheritdoc
   */
  public function getDistributor(AccountInterface $user) {
    $distributor = null;

    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_distributor')
      ->condition('user_id', $user->id());
    $ids = $query->execute();

    if (count($ids) !== 0) {
      $distributor = Distributor::load(array_pop($ids));
    }

    return $distributor;
  }

  public function cancelDistribution(OrderInterface $commerce_order, $is_completed) {
    // 分两种情况：
    // 1、从completed到 cancel
    // 2、从其他状态到 cancel

    // 二者都要处理：把分佣款记为 不可用 status->false
    // 前者：把账目从主账户出账
    // 后者：把账目从预计账户出账

    if ($this->isDistributed($commerce_order)) {

      $events = $this->getOrderEvents($commerce_order);

      foreach ($events as $event) {
        $commissions = $this->getEventCommissions($event);

        foreach ($commissions as $commission) {
          $pending_account = $this->financeFinanceManager->getAccount($commission->getDistributor()->getOwner(), self::FINANCE_PENDING_ACCOUNT_TYPE);
          $main_account = $this->financeFinanceManager->getAccount($commission->getDistributor()->getOwner(), self::FINANCE_ACCOUNT_TYPE);

          $process_account = $pending_account;
          if ($is_completed) {
            $process_account = $main_account;
          }
          $this->financeFinanceManager->createLedger($process_account, Ledger::AMOUNT_TYPE_CREDIT, $commission->getAmount(), '订单取消，取消佣金。（佣金信息：' . $commission->getName() . '）', $commission);

          $commission->setValid(false);
          $commission->save();
        }
      }
    }
  }

  public function upgradeAsLeader(Distributor $distributor) {
    $leader = self::getLeader($distributor);
    if (!$leader) {
      $leader = Leader::create([
        'distributor_id' => $distributor,
        'name' => $distributor->getName()
      ]);

      $leader->save();
    }

    $distributor->setIsLeader(true);
    $distributor->save();

    return $leader;
  }

  /**
   * 把订单的记账金额从预计账户移到主账户
   *
   * @param OrderInterface $commerce_order
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function transferPendingDistribution(OrderInterface $commerce_order) {
    $events = $this->getOrderEvents($commerce_order);

    foreach ($events as $event) {
      $commissions = $this->getEventCommissions($event);

      foreach ($commissions as $commission) {
        $pending_account = $this->financeFinanceManager->getAccount($commission->getDistributor()->getOwner(), self::FINANCE_PENDING_ACCOUNT_TYPE);
        $main_account = $this->financeFinanceManager->getAccount($commission->getDistributor()->getOwner(), self::FINANCE_ACCOUNT_TYPE);

        // 转账到主账户
        $this->financeFinanceManager->transfer($pending_account, $main_account, $commission->getAmount(), '订单完成，佣金由预计账户转入主账户。（分佣信息：' . $commission->getName() . '）', $commission);
      }
    }
  }

  /**
   * @param OrderInterface $commerce_order
   * @return Event[]
   */
  public function getOrderEvents(OrderInterface $commerce_order) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_event')
      ->condition('order_id', $commerce_order->id());
    $ids = $query->execute();

    if (count($ids)) {
      return Event::loadMultiple($ids);
    } else {
      return [];
    }
  }

  /**
   * @param Event $event
   * @return Commission[]
   */
  public function getEventCommissions(Event $event) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_commission')
      ->condition('event_id', $event->id());
    $ids = $query->execute();

    if (count($ids)) {
      return Commission::loadMultiple($ids);
    } else {
      return [];
    }
  }

  /**
   * @param OrderInterface $commerce_order
   * @return Price
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function countOrderCommissionsAmount(OrderInterface $commerce_order) {
    $amount = new Price('0.00', 'CNY');

    $events = $this->getOrderEvents($commerce_order);
    foreach ($events as $event) {
      $commissions = $this->getEventCommissions($event);
      foreach ($commissions as $commission) {
        $amount = $amount->add($commission->getAmount());
      }
    }

    return $amount;
  }

  /**
   * 计算已推广的用户数量
   * @param Distributor $distributor
   * @param null $recent days
   * @return Int
   * @throws \Exception
   */
  public function countPromoters(Distributor $distributor, $recent = null) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_promoter')
      ->condition('distributor_id', $distributor->id());

    if ($recent) {
      $now = new \DateTime();
      $recent_time = $now->sub(new \DateInterval('P' . $recent . 'D'));
      $query->condition('created', $recent_time->getTimestamp(), '>=');
    }

    return $query->count()->execute();
  }

  public function countOrders(Distributor $distributor, $recent = null) {
    // 找出关联用户
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('distribution_promoter')
      ->condition('distributor_id', $distributor->id());

    $ids = $query->execute();

    if (count($ids)) {

      $user_ids = [];

      $promoters = Promoter::loadMultiple($ids);
      foreach ($promoters as $promoter) {
        /** @var Promoter $promoter */
        $user_ids[] = $promoter->getUser()->id();
      }

      /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
      $query = \Drupal::entityQuery('commerce_order')
        ->condition('state', 'draft', '<>')
        ->condition('uid', $user_ids, 'IN');

      if ($recent) {
        $now = new \DateTime();
        $recent_time = $now->sub(new \DateInterval('P' . $recent . 'D'));
        $query->condition('created', $recent_time->getTimestamp(), '>=');
      }

      return $query->count()->execute();
    } else {
      return 0;
    }
  }

  /**
   * @param Distributor $distributor
   * @param null $type
   * @param null $recent
   * @return Price
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function countCommissionTotalAmount(Distributor $distributor, $type = null, $recent = null) {
    $query = \Drupal::entityQuery('distribution_commission')
      ->condition('distributor_id', $distributor->id());

    if ($type) {
      $query->condition('type', $type);
    }

    if ($recent) {
      $now = new \DateTime();
      $recent_time = $now->sub(new \DateInterval('P' . $recent . 'D'));
      $query->condition('created', $recent_time->getTimestamp(), '>=');
    }

    $ids = $query->execute();

    $commissions = Commission::loadMultiple($ids);

    $price = new Price('0.00', 'CNY');
    foreach ($commissions as $commission) {
      /** @var Commission $commission */
      $price = $price->add($commission->getAmount());
    }

    return $price;
  }
}
