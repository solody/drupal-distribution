<?php

namespace Drupal\distribution;

use Drupal;
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
   * @inheritdoc
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws Drupal\Core\Entity\EntityStorageException
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
   * @inheritdoc
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createOrderAchievement(DistributorInterface $distributor, OrderInterface $commerce_order) {
    $acceptances = $this->getDistributorAcceptances($distributor);
    foreach ($acceptances as $acceptance) {
      // 跳过已完成的任务
      if ($acceptance->isCompleted()) continue;

      // 防止重复处理
      $achievement = $this->getAcceptanceOrderAchievement($acceptance, $commerce_order);
      if ($achievement instanceof Achievement) continue;

      $score = $acceptance->computeScore($commerce_order);
      if ($score) {
        $achievement = Achievement::create([
          'acceptance_id' => $acceptance,
          'score' => $score,
          'source_id' => $commerce_order,
          'status' => true
        ]);
        $achievement->save();

        // 更新任务总分缓存
        $acceptance->addAchievement($achievement);
        $acceptance->save(); // 保存前，会自动检查完成条件，并设置完成状态
      }
    }
  }

  /**
   * @inheritdoc
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws Drupal\Core\Entity\EntityStorageException
   */
  public function cancelOrderAchievement(DistributorInterface $distributor, OrderInterface $commerce_order) {
    $acceptances = $this->getDistributorAcceptances($distributor);
    foreach ($acceptances as $acceptance) {
      // 跳过已完成的任务
      if ($acceptance->isCompleted()) continue;

      $achievement = $this->getAcceptanceOrderAchievement($acceptance, $commerce_order);

      if ($achievement instanceof Achievement) {
        $achievement->setValid(false);
        $achievement->save();

        // 更新任务总分缓存
        $acceptance->subtractAchievement($achievement);
        $acceptance->save(); // 保存前，会自动检查完成条件，并设置完成状态
      }
    }
  }

  /**
   * @inheritdoc
   * @throws Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAcceptanceOrderAchievement(AcceptanceInterface $acceptance, OrderInterface $commerce_order) {
    $query = Drupal::entityTypeManager()->getStorage('distribution_achievement')->getQuery();
    $query->condition('acceptance_id', $acceptance->id())
      ->condition('source_id__target_id', $commerce_order->id());

    $ids = $query->execute();

    if (count($ids)) {
      return Achievement::load(array_pop($ids));
    } else {
      return null;
    }
  }

  /**
   * @inheritdoc
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
   * @inheritdoc
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getNewcomerTasks() {
    $tasks = Drupal::entityTypeManager()->getStorage('distribution_task')->loadByProperties([
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
   * @inheritdoc
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getDistributorAcceptances(DistributorInterface $distributor) {
    $acceptances = Drupal::entityTypeManager()->getStorage('distribution_acceptance')->loadByProperties([
      'distributor_id' => $distributor->id()
    ]);

    if (is_array($acceptances) && count($acceptances)) {
      return array_values($acceptances);
    } else {
      return [];
    }
  }
}
