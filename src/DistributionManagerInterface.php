<?php

namespace Drupal\distribution;

use Drupal\commerce_price\Price;
use Drupal\Core\Session\AccountInterface;
use Drupal\distribution\Entity\AcceptanceInterface;
use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Entity\Event;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\distribution\Entity\MonthlyStatementInterface;
use Drupal\distribution\Entity\Promoter;
use Drupal\distribution\Entity\PromoterInterface;
use Drupal\distribution\Entity\Target;
use Drupal\user\Entity\User;

/**
 * Interface DistributionManagerInterface.
 */
interface DistributionManagerInterface {
  /**
   * 对一个订单进行分销数据创建处理
   *
   * @param OrderInterface $commerce_order
   * @return mixed
   */
  public function distribute(OrderInterface $commerce_order);

  public function cancelDistribution(OrderInterface $commerce_order, $is_completed);

  public function createEvent(OrderItemInterface $commerce_order_item, Distributor $distributor);

  public function createCommissions(Event $distributionEvent);

  /**
   * 创建任务奖励
   * @param AcceptanceInterface $acceptance
   */
  public function createTaskCommissions(AcceptanceInterface $acceptance);

  /**
   * 创建月度奖励
   * @param MonthlyStatementInterface $monthly_statement
   * @param DistributorInterface $distributor
   * @param Price $amount
   * @param string $remarks
   * @return mixed
   */
  public function createMonthlyRewardCommission(MonthlyStatementInterface $monthly_statement, DistributorInterface $distributor, Price $amount, $remarks = '');

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
   * @return Target|null
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
  public function createPromoter(Distributor $distributor, AccountInterface $user);

  /**
   * 创建分销用户
   *
   * @param User $user
   * @param Distributor $upstream_distributor
   * @param string $state
   * @param array $agent
   * @return mixed
   */
  public function createDistributor(AccountInterface $user, Distributor $upstream_distributor, $state = 'draft', $agent = []);

  /**
   * 查找分销用户
   * @param AccountInterface $user
   * @return Distributor|null
   */
  public function getDistributor(AccountInterface $user);

  /**
   * 通过手机查找分销用户
   * @param $phone
   * @return Distributor|null
   */
  public function getDistributorByPhone($phone);

  /**
   * @param AccountInterface $user
   * @return PromoterInterface[]|static[]
   */
  public function getPromoters(AccountInterface $user);

  /**
   * @param Distributor $distributor
   * @return \Drupal\Core\Entity\EntityInterface[]|Promoter[]
   */
  public function getPromotedUsers(Distributor $distributor);

  /**
   * @param AccountInterface $user
   * @return Promoter|null
   */
  public function getLastPromoter(AccountInterface $user);
}
