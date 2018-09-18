<?php

namespace Drupal\distribution\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\distribution\DistributionManager;
use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\MonthlyRewardManagerInterface;
use Drupal\distribution\TaskManagerInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderSubscriber.
 */
class OrderSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\distribution\DistributionManagerInterface
   */
  protected $distributionManager;

  /**
   * @var TaskManagerInterface
   */
  protected $taskManager;

  /**
   * @var MonthlyRewardManagerInterface
   */
  protected $monthlyRewardManager;

  /**
   * Constructs a new OrderSubscriber object.
   * @param DistributionManager $distribution_manager
   */
  public function __construct(DistributionManager $distribution_manager, TaskManagerInterface $task_manager, MonthlyRewardManagerInterface $monthly_reward_manager) {
    $this->distributionManager = $distribution_manager;
    $this->taskManager = $task_manager;
    $this->monthlyRewardManager = $monthly_reward_manager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['commerce_order_place_post_transition'];
    $events['commerce_order.complete.post_transition'] = ['commerce_order_complete_post_transition'];
    $events['commerce_order.fulfill.post_transition'] = ['commerce_order_complete_post_transition'];
    $events['commerce_order.cancel.pre_transition'] = ['commerce_order_cancel_pre_transition'];

    return $events;
  }

  /**
   * This method is called whenever the commerce_order.place.post_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function commerce_order_place_post_transition(WorkflowTransitionEvent $event) {
    /** @var Order $order */
    $order = $event->getEntity();

    // 不管什么订单流，place即付款，付款即处理分销
    $this->handleOrderDistribution($order);

    // 处理任务成绩
    $this->handleTask($order);

    switch ($event->getWorkflow()->getId()) {
      case 'order_default':
        // place后的状态是完成状态
        $this->transferPendingDistribution($order);
        break;

      case 'order_default_validation':

        break;

      case 'order_fulfillment':

        break;

      case 'order_fulfillment_validation':

        break;

      default:
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

    // 处理任务成绩
    $this->handleTask($order);

    // 处理月度奖金
    $this->handleMonthlyReward($order);

    // 把预计佣金进行到账处理
    $this->transferPendingDistribution($order);
  }

  /**
   * This method is called whenever the commerce_order.cancel.pre_transition event is
   * dispatched.
   *
   * @param WorkflowTransitionEvent $event
   */
  public function commerce_order_cancel_pre_transition(WorkflowTransitionEvent $event) {
    /** @var Order $order */
    $order = $event->getEntity();
    $this->cancelOrderDistribution($order, $event->getFromState()->getId() === 'completed');
  }

  /**
   * 处理分销推广佣金、链级佣金、团队领导奖金的预计，它们会在订单完成后统一从预计账户转账到余额账户
   * @param OrderInterface $order
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function handleOrderDistribution(OrderInterface $order) {
    // 创建分佣项，把记账记到预计账户
    $config = \Drupal::config('distribution.settings');

    // 如果开启了分销，并且不是匿名订单
    if ($config->get('enable') && !$order->getCustomer()->isAnonymous()) {

      // 对订单进行分佣处理、任务成绩处理
      $this->distributionManager->distribute($order);
    }
  }

  /**
   * 处理任务成绩
   * @param OrderInterface $order
   */
  private function handleTask(OrderInterface $order) {
    $config = \Drupal::config('distribution.settings');
    if ($config->get('enable') && !$order->getCustomer()->isAnonymous()) {
      // 创建任务成绩
      $this->taskManager->createOrderAchievement($order);
    }
  }

  /**
   * 月度奖金处理（$order 必须是已经保存有 distributor 字段的）
   * 1、为订单创建月度奖金池金额，提升分销用户的奖励条件值、奖金分配比值
   * @param OrderInterface $order
   */
  private function handleMonthlyReward(OrderInterface $order) {
    $config = \Drupal::config('distribution.settings');
    // 如果开启了月度奖金，那么处理月度奖金
    if ($config->get('enable') && $config->get('commission.monthly_reward')) {
      $this->monthlyRewardManager->handleDistribution($order);
    }
  }

  /**
   * 把订单的分销佣金记账金额从预计账户移到主账户
   * @param OrderInterface $order
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function transferPendingDistribution(OrderInterface $order) {
    // 检查订单在place时有没有分佣
    if ($this->distributionManager->isDistributed($order)) {
      // 如果有，那么处理记账数据，把订单的记账金额从预计账户移到主账户
      $this->distributionManager->transferPendingDistribution($order);
    }
  }

  /**
   * 取消佣金，取消任务成绩
   * @param OrderInterface $order
   * @param $isCompletedOrder
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function cancelOrderDistribution(OrderInterface $order, $isCompletedOrder) {
    $this->distributionManager->cancelDistribution($order, $isCompletedOrder);
  }
}
