<?php

namespace Drupal\distribution;

use Drupal\distribution\Entity\AcceptanceInterface;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Entity\TaskInterface;

/**
 * Interface TaskManagerInterface.
 */
interface TaskManagerInterface {

  /**
   * 接受所有未领取的新手任务
   * @param DistributorInterface $distributor
   */
  public function acceptNewcomerTasks(DistributorInterface $distributor);

  /**
   * 领取一个任务
   * @param DistributorInterface $distributor
   * @param TaskInterface $task
   * @return AcceptanceInterface
   */
  public function acceptTask(DistributorInterface $distributor, TaskInterface $task);

  /**
   * 获取所有已启用的新手任务
   * @return TaskInterface[]
   */
  public function getNewcomerTasks();

  /**
   * 获取用户的所有任务领取记录
   * @param DistributorInterface $distributor
   * @return AcceptanceInterface[]
   */
  public function getDistributorAcceptances(DistributorInterface $distributor);
}
