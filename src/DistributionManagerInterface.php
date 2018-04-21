<?php

namespace Drupal\distribution;

use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\Event;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\user\Entity\User;

/**
 * Interface DistributionManagerInterface.
 */
interface DistributionManagerInterface
{
    /**
     * 对一个订单进行分销数据创建处理
     *
     * @param OrderInterface $commerce_order
     * @return mixed
     */
    public function distribute(OrderInterface $commerce_order);

    public function createEvent(OrderItemInterface $commerce_order_item);

    public function createCommissions(Event $distributionEvent);

    /**
     * 计算需要分佣的分销商，和应分得的金额
     *
     * @param Distributor $distributor
     * @return mixed
     */
    public function computeCommissionDistributors(Distributor $distributor, $amount);

    /**
     * 如果已存在 DistributionTarget，更新金额，
     * 否则创建新的 DistributionTarget。
     *
     * @param PurchasableEntityInterface $purchasableEntity
     * @param $amount
     * @return mixed
     */
    public function setTarget(PurchasableEntityInterface $purchasableEntity, $amount);

    /**
     * 获取 DistributionTarget，用于读取商品的可分成金额
     *
     * @param PurchasableEntityInterface $purchasableEntity
     * @return mixed
     */
    public function getTarget(PurchasableEntityInterface $purchasableEntity);

    /**
     * 依靠分销商与消费者的推广关系判定订单的直属分销商
     *
     * @param OrderInterface $commerce_order
     * @return mixed
     */
    public function determineDistributor(OrderInterface $commerce_order);

    /**
     * 为用户创建推广者
     *
     * @param Distributor $distributor
     * @param User $user
     * @return mixed
     */
    public function createPromoter(Distributor $distributor, User $user);
}
