<?php

namespace Drupal\distribution\EventSubscriber;

use Drupal\distribution\Event\TaskAcceptanceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\distribution\DistributionManagerInterface;

/**
 * Class TaskAcceptanceSubscriber.
 */
class TaskAcceptanceSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\distribution\DistributionManagerInterface definition.
   *
   * @var \Drupal\distribution\DistributionManagerInterface
   */
  protected $distributionDistributionManager;

  /**
   * Constructs a new TaskAcceptanceSubscriber object.
   */
  public function __construct(DistributionManagerInterface $distribution_distribution_manager) {
    $this->distributionDistributionManager = $distribution_distribution_manager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[TaskAcceptanceEvent::ACCEPTANCE_COMPLETE] = ['distribution_task_acceptance_complete'];

    return $events;
  }

  /**
   * This method is called whenever the distribution.task.acceptance.complete event is
   * dispatched.
   *
   * @param TaskAcceptanceEvent $event
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function distribution_task_acceptance_complete(TaskAcceptanceEvent $event) {
    // 任务完成，执行任务奖励
    $this->distributionDistributionManager->createTaskCommissions($event->getAcceptance());
    // 如果任务可以升级会员身份，处理升级
    if ($event->getAcceptance()->getTask()->isUpgrade()) {
      $distributor = $event->getAcceptance()->getDistributor();
      if (!$distributor->isSenior()) {
        $distributor->setSenior(true);
        $distributor->save();
      }
    }
  }

}
