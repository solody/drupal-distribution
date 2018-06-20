<?php

namespace Drupal\distribution;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\distribution\Entity\Acceptance;
use Drupal\distribution\Entity\AcceptanceInterface;
use Drupal\distribution\Entity\Achievement;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Entity\TaskInterface;

/**
 * Class TaskManager.
 */
class TaskManager implements TaskManagerInterface {

  /**
   * Constructs a new TaskManager object.
   */
  public function __construct() {

  }

  /**
   * 接受所有未领取的新手任务
   * @param DistributorInterface $distributor
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function acceptNewcomerTasks(DistributorInterface $distributor) {
    $acceptances = $this->getDistributorAcceptances($distributor);
    foreach ($this->getNewcomerTasks() as $task) {
      $accepted = false;
      foreach ($acceptances as $acceptance) {
        if ($task->id() === $acceptance->getTaskId()) $accepted = true;
      }
      if (!$accepted) $this->acceptTask($distributor, $task);
    }
  }

  /**
   * 创建任务成绩
   * @param DistributorInterface $distributor
   * @param OrderInterface $commerce_order
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function handleOrderAchievement(DistributorInterface $distributor, OrderInterface $commerce_order) {
    $acceptances = $this->getDistributorAcceptances($distributor);
    foreach ($acceptances as $acceptance) {
      $score = $acceptance->computeScore($commerce_order);
      if ($score) {
        $achievement = Achievement::create([
          'acceptance_id' => $acceptance,
          'score' => $score,
          'source_id' => $commerce_order,
          'status' => true
        ]);
        $achievement->save();
        $acceptance->addAchievement($achievement);
        $acceptance->save(); // 保存前，会自动检查完成条件，并设置完成状态
      }
    }
  }

  /**
   * 领取一个任务
   * @param DistributorInterface $distributor
   * @param TaskInterface $task
   * @return AcceptanceInterface
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function acceptTask(DistributorInterface $distributor, TaskInterface $task) {
    $acceptance = Acceptance::create([
      'distributor_id' => $distributor,
      'task_id' => $task,
      'achievement' => 0,
      'status' => false
    ]);
    $acceptance->save();
    return $acceptance;
  }

  /**
   * 获取所有已启用的新手任务
   * @return TaskInterface[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getNewcomerTasks() {
    $tasks = \Drupal::entityTypeManager()->getStorage('distribution_task')->loadByProperties([
      'newcomer' => true,
      'status' => true
    ]);

    if (is_array($tasks) && count($tasks)) {
      return array_values($tasks);
    } else {
      return [];
    }
  }

  /**
   * 获取用户的所有任务领取记录
   * @param DistributorInterface $distributor
   * @return AcceptanceInterface[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getDistributorAcceptances(DistributorInterface $distributor) {
    $acceptances = \Drupal::entityTypeManager()->getStorage('distribution_acceptance')->loadByProperties([
      'distributor_id' => $distributor->id()
    ]);

    if (is_array($acceptances) && count($acceptances)) {
      return array_values($acceptances);
    } else {
      return [];
    }
  }
}
