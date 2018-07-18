<?php

namespace Drupal\distribution\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\distribution\DistributionManager;
use Drupal\distribution\Entity\Distributor;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\distribution\DistributionManagerInterface;

/**
 * Class OrderSubscriber.
 */
class OrderSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\distribution\DistributionManager definition.
   *
   * @var \Drupal\distribution\DistributionManagerInterface
   */
  protected $distributionManager;

  /**
   * Constructs a new OrderSubscriber object.
   * @param DistributionManager $distribution_manager
   */
  public function __construct(DistributionManager $distribution_manager) {
    $this->distributionManager = $distribution_manager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['commerce_order_place_post_transition'];
    $events['commerce_order.complete.post_transition'] = ['commerce_order_complete_post_transition'];
    $events['commerce_order.cancel.pre_transition'] = ['commerce_order_cancel_pre_transition'];

    return $events;
  }

  /**
   * This method is called whenever the commerce_order.place.post_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function commerce_order_place_post_transition(WorkflowTransitionEvent $event) {
    // 创建分佣项，把记账记到预计账户
    $config = \Drupal::config('distribution.settings');

    if ($config->get('enable')) {
      /** @var Order $order */
      $order = $event->getEntity();

      // 对订单进行分佣处理
      $this->distributionManager->distribute($order);

      // TODO:: 检查订单是否有任务成绩，添加成绩

      // 检查配置，如果开启了自动转化，那么创建分销用户
      if ($config->get('transform.auto')) {
        // 如果订单购买者已经是分销商，无须转化
        if (!$this->distributionManager->getDistributor($order->getCustomer())) {
          /** @var Distributor $upstream_distributor */
          $distributor = $this->distributionManager->determineDistributor($order);

          $this->distributionManager
            ->createDistributor($order->getCustomer(), $distributor, 'approved');
        }
      }
    }
  }

  /**
   * This method is called whenever the commerce_order.complete.post_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function commerce_order_complete_post_transition(WorkflowTransitionEvent $event) {
    /** @var Order $order */
    $order = $event->getEntity();

    // 检查订单在place时有没有分佣
    if ($this->distributionManager->isDistributed($order)) {
      // 如果有，那么处理记账数据，把订单的记账金额从预计账户移到主账户
      $this->distributionManager->transferPendingDistribution($order);
    }
  }

  /**
   * This method is called whenever the commerce_order.cancel.pre_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function commerce_order_cancel_pre_transition(WorkflowTransitionEvent $event) {
    // 订单取消，取消佣金
    /** @var Order $order */
    $order = $event->getEntity();
    $this->distributionManager->cancelDistribution($order, $event->getFromState()->getId() === 'completed');
  }

}
