<?php

namespace Drupal\distribution;

use Drupal\commerce_price\Price;
use Drupal\Core\Session\AccountInterface;
use Drupal\distribution\Entity\Leader;
use Drupal\distribution\Entity\LeaderInterface;
use Drupal\distribution\Entity\PromoterInterface;
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
class DistributionManager implements DistributionManagerInterface
{

    /**
     * Drupal\finance\FinanceManagerInterface definition.
     *
     * @var \Drupal\finance\FinanceManagerInterface
     */
    protected $financeFinanceManager;

    /**
     * Constructs a new DistributionManager object.
     */
    public function __construct(FinanceManagerInterface $finance_finance_manager)
    {
        $this->financeFinanceManager = $finance_finance_manager;
    }

    /**
     * @inheritdoc
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function distribute(OrderInterface $commerce_order)
    {
        // 检查系统是否开启分销
        $config = \Drupal::config('distribution.settings');

        if ($config->get('enable')) {
            // 检查订单是否已经处理过佣金，防止重复处理
            if ($this->isDistributed($commerce_order)) return;

            // 检查订单能否确定上级分销用户
            $distributor = $this->determineDistributor($commerce_order);
            if (!$distributor) return;

            foreach ($commerce_order->getItems() as $orderItem) {
                $this->createEvent($orderItem, $distributor);
            }
        }
    }

    public function isDistributed(OrderInterface $commerce_order)
    {
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
    public function createEvent(OrderItemInterface $commerce_order_item, Distributor $distributor)
    {
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
     * @return Price
     */
    public function computeCommissionAmount(Target $target, $commission_type, Price $price)
    {
        // 检查配置的计算模式
        $config = \Drupal::config('distribution.settings');

        $computed_price = new Price('0.00', $price->getCurrencyCode());

        if ($config->get('commission.compute_mode') === 'fixed_amount') {
            // 固定金额，直接取已设置的固定金额
            switch ($commission_type) {
                case Commission::TYPE_PROMOTION:
                    $computed_price->add($target->getAmountPromotion());
                    break;
                case Commission::TYPE_CHAIN:
                    $computed_price->add($target->getAmountChain());
                    break;
                case Commission::TYPE_LEADER:
                    $computed_price->add($target->getAmountLeader());
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
                        $chain_price = new Price($price->getNumber() * $chain_percentage / 100,$price->getCurrencyCode());

                        $computed_price->add(new Price($chain_price->getNumber() * $percentage / 100,$chain_price->getCurrencyCode()));
                    } else {
                        $computed_price->add(new Price($price->getNumber() * $percentage / 100,$price->getCurrencyCode()));
                    }
                    break;
                case Commission::TYPE_CHAIN:
                    if ($target->getPercentageChain()) {
                        $percentage = $target->getPercentageChain();
                    }
                    $computed_price->add(new Price($price->getNumber() * $percentage / 100,$price->getCurrencyCode()));
                    break;
                case Commission::TYPE_LEADER:
                    if ($target->getPercentageLeader()) {
                        $percentage = $target->getPercentageLeader();
                    }
                    $computed_price->add(new Price($price->getNumber() * $percentage / 100,$price->getCurrencyCode()));
                    break;
            }
        }

        return $computed_price;
    }

    public function createCommissions(Event $distributionEvent)
    {
        // 检查需要产生的佣金类型
        $config = \Drupal::config('distribution.settings');

        // 推广佣金
        if ($config->get('commission.promotion')) {
            // 非分销用户成交的订单，才能产生推广佣金
            if (!$this->getDistributor($distributionEvent->getOrder()->getCustomer())) {
                // 读取推广者
                $promoters = $this->getPromoters($distributionEvent->getOrder()->getCustomer());
                // 平分佣金
                $amount = new Price($distributionEvent->getAmountPromotion()->getNumber() / count($promoters), $distributionEvent->getAmountPromotion()->getCurrencyCode());

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
                }
            }
        }

        // 链级佣金
        if ($config->get('commission.chain')) {
            // 计算分佣链级
            $chain_commission_levels = $this->computeChainCommissionLevels($distributionEvent->getDistributor(), $distributionEvent->getAmountChain());

            foreach ($chain_commission_levels as $chain_commission_level) {
                $commission = Commission::create([
                    'event_id' => $distributionEvent->id(),
                    'type' => 'chain',
                    'distributor_id' => $chain_commission_level['distributor'],
                    'name' => $distributionEvent->getName() . '：链级佣金 ' . $chain_commission_level['remark'],
                    'amount' => $chain_commission_level['amount']
                ]);
                $commission->save();
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
                    'name' => $distributionEvent->getName() . '：团队领导佣金 ' . $distributionEvent->getAmountLeader()->getCurrencyCode().$distributionEvent->getAmountLeader()->getNumber(),
                    'amount' => $distributionEvent->getAmountLeader(),
                    'leader_id' =>$leader->id()
                ]);
                $commission->save();
            }
        }
    }

    /**
     * @param Distributor $distributor
     * @return LeaderInterface|null|static
     */
    public static function computeLeader(Distributor $distributor)
    {
        $leader = null;
        $upstream_distributor = $distributor->getUpstreamDistributor();

        while(!$leader && $upstream_distributor) {
            $leader = self::getLeader($upstream_distributor);
            $upstream_distributor = $upstream_distributor->getUpstreamDistributor();
        }

        return $leader;
    }

    /**
     * @param Distributor $distributor
     * @return LeaderInterface|null|static
     */
    public static function getLeader(Distributor $distributor)
    {
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

    public function computeChainCommissionLevels(Distributor $distributor, Price $amount)
    {
        $setting = \Drupal::config('distribution.setting');

        $levels = [];
        for ($i = 1; $i <4; $i++) {
            $commission_distributor = self::getUpstreamDistributor($distributor, $i);
            if (!$commission_distributor) break;

            $percentage = (float)$setting->get('chain_commission.level_' . $i);
            $commission_amount = new Price($amount->getNumber() * ($percentage / 100), $amount->getCurrencyCode());

            $levels[$i] = [
                'distributor' => $commission_distributor,
                'percentage' => $percentage,
                'amount' => $commission_amount,
                'remark' => $i . '级分佣，' . $percentage . '% x ' . $amount
            ];

            $amount = $amount->subtract($commission_amount);
        }
    }

    public static function getUpstreamDistributor(Distributor $distributor, $level = 1)
    {
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
    public function setTarget(PurchasableEntityInterface $purchasableEntity, $amount)
    {
        $target = $this->getTarget($purchasableEntity);

        if (!$target) {
            $target = Target::create([
                'purchasable_entity' => $purchasableEntity,
                'amount' => $amount
            ]);
        } else {
            $target->set('amount', $amount);
        }

        $target->save();

        return $target;
    }

    /**
     * @inheritdoc
     */
    public function getTarget(PurchasableEntityInterface $purchasableEntity)
    {
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
    public function determineDistributor(OrderInterface $commerce_order)
    {
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
                $distributor = Distributor::load(array_pop($ids));
            }
        }

        return $distributor;
    }

    /**
     * @inheritdoc
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function createPromoter(Distributor $distributor, AccountInterface $user)
    {
        /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
        $query = \Drupal::entityQuery('distribution_promoter')
            ->condition('distributor_id', $distributor->id())
            ->condition('user_id', $user->id());
        $ids = $query->execute();

        if (count($ids) === 0) {
            $promoter = Promoter::create([
                'distributor_id' => $distributor->id(),
                'user_id' => $user->id()
            ]);

            $promoter->save();
        }
    }

    /**
     * @param AccountInterface $user
     * @return PromoterInterface[]|static[]
     */
    public function getPromoters(AccountInterface $user)
    {
        /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
        $query = \Drupal::entityQuery('distribution_promoter')
            ->condition('user_id', $user->id());

        $ids = $query->execute();

        if (count($ids)) {
            return Promoter::loadMultiple($ids);
        }
    }

    /**
     * @inheritdoc
     */
    public function createDistributor(AccountInterface $user, Distributor $upstream_distributor = null, $state = 'draft', $agent = [])
    {
        $distributor = $this->getDistributor($user);

        if (!$distributor) {
            $level_number = 1;
            if ($upstream_distributor) $level_number += $upstream_distributor->getLevelNumber();

            $price = new Price('0.00', 'CNY');

            $data = [
                'user_id' => $user->id(),
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

            Distributor::create($data);
        }

        return $distributor;
    }

    /**
     * @inheritdoc
     */
    public function getDistributor(AccountInterface $user)
    {
        $distributor = null;

        /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
        $query = \Drupal::entityQuery('distribution_distributor')
            ->condition('user_id', $user->id());
        $ids = $query->execute();

        if (count($ids) === 0) {
            $distributor = Distributor::load(array_pop($ids));
        }

        return $distributor;
    }

    public function cancelDistribution(OrderInterface $commerce_order)
    {
        // TODO: Implement cancelDistribution() method.
    }
}
