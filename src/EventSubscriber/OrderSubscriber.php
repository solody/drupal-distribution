<?php

namespace Drupal\distribution\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\distribution\DistributionManagerInterface;

/**
 * Class OrderSubscriber.
 */
class OrderSubscriber implements EventSubscriberInterface
{

    /**
     * Drupal\distribution\DistributionManagerInterface definition.
     *
     * @var \Drupal\distribution\DistributionManagerInterface
     */
    protected $distributionManager;

    /**
     * Constructs a new OrderSubscriber object.
     * @param DistributionManagerInterface $distribution_manager
     */
    public function __construct(DistributionManagerInterface $distribution_manager)
    {
        $this->distributionManager = $distribution_manager;
    }

    /**
     * {@inheritdoc}
     */
    static function getSubscribedEvents()
    {
        $events['commerce_order.place.post_transition'] = ['commerce_order_place_post_transition'];
        $events['commerce_order.cancel.pre_transition'] = ['commerce_order_cancel_pre_transition'];

        return $events;
    }

    /**
     * This method is called whenever the commerce_order.place.post_transition event is
     * dispatched.
     *
     * @param WorkflowTransitionEvent $event
     */
    public function commerce_order_place_post_transition(WorkflowTransitionEvent $event)
    {
        // 检查配置，如果开启了自动转化，那么创建分销用户
        $config = \Drupal::config('distribution.settings');

        if ($config->get('transform.auto')) {
            /** @var Order $order */
            $order = $event->getEntity();
            $this->distributionManager
                ->createDistributor($order->getCustomer(), null, 'approved');
        }
    }

    /**
     * This method is called whenever the commerce_order.cancel.pre_transition event is
     * dispatched.
     *
     * @param WorkflowTransitionEvent $event
     */
    public function commerce_order_cancel_pre_transition(WorkflowTransitionEvent $event)
    {
        // 订单取消，取消佣金

        /** @var Order $order */
        $order = $event->getEntity();
        $this->distributionManager->cancelDistribution($order);
    }

}
