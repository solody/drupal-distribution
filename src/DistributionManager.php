<?php

namespace Drupal\distribution;

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


    public function distribute(OrderInterface $commerce_order)
    {
        // 检查系统是否开启分销
        $config = \Drupal::config('aiqilv_commerce_distribution.setting');
        if (!$config['enable']) return;

        // 检查客户是否绑定了分销商
        if (!$this->determineDistributor($commerce_order)) return;

        foreach ($commerce_order->getItems() as $orderItem) {
            $this->createEvent($orderItem);
        }
    }

    public function createEvent(OrderItemInterface $commerce_order_item)
    {
        $target = $this->getTarget($commerce_order_item->getPurchasedEntity());

        // 如果商品没有设置分成，中止分佣
        if (!$target) return;

        $event = DistributionEvent::create([
            'order_id' => $commerce_order_item->getOrderId(),
            'order_item_id' => $commerce_order_item->id(),
            'distributor_id' => $this->determineDistributor($commerce_order_item->getOrder()),
            'target_id' => $target,
            'amount' => $commerce_order_item->getTotalPrice()->getNumber()
        ]);

        $event->save();

        $this->createCommissions($event);
    }

    public function createCommissions(DistributionEvent $distributionEvent)
    {
        $commissionConfig = $this->computeCommissionDistributors(
            $distributionEvent->get('distributor_id')[0]->entity,
            $distributionEvent->getAmount()->getNumber());

        foreach ($commissionConfig as $item) {
            $commission = DistributionCommission::create([
                'event_id' => $distributionEvent->id(),
                'distributor_id' => $item['distributor']->id(),
                'title' => $item['remark'],
                'percent' => $item['percent'],
                'amount' => [
                    'number' => $item['amount'],
                    'currency_code' => 'CNY'
                ]
            ]);

            $commission->save();

            // 调用账户服务接口，生成账户账目数据
            // 分销商收入账户，增加进项
            $this->financeManager->createLedger(
                $this->financeManager->getAccount($item['distributor']->get('user_id')[0]->entity, 'distributor'),
                LedgerAmountType::DEBIT,
                $item['amount'],
                $item['remark'],
                $distributionEvent->get('order_id')[0]->entity
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function computeCommissionDistributors(DistributionDistributor $distributor, $amount)
    {
        // 读取链级分佣百分比设置
        $setting = \Drupal::config('aiqilv_commerce_distribution.setting');
        $config = [1 => $setting['percent_level_1']]; // 暂时只需1级分佣

        // 查找链级分销商
        $distributors = [1 => $distributor];  // 暂时只有一级分佣

        return [
            1 => [
                'distributor' => $distributors[1],
                'percent' => $config[1],
                'amount' => $amount * ($config[1] / 100),
                'remark' => '1级分佣，' . $config[1] . '% x ' . $amount
            ]
        ];
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
            ->condition('purchasable_entity', $purchasableEntity->id());
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
        /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
        $query = \Drupal::entityQuery('distribution_promoter')
            ->condition('user_id', $commerce_order->getCustomerId())
            ->sort('id', 'DESC');
        $ids = $query->execute();

        $distributor = null;
        if (count($ids)) {
            $distributor = Distributor::load(array_pop($ids));
        }

        return $distributor;
    }

    /**
     * @inheritdoc
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function createPromoter(Distributor $distributor, User $user)
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
}
