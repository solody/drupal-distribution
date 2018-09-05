<?php

namespace Drupal\distribution\Plugin\TaskType;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\distribution\Entity\TaskInterface;
use Drupal\distribution\Plugin\TaskTypeBase;
use Drupal\entity\BundleFieldDefinition;

/**
 * @TaskType(
 *   id = "downstream_quantity",
 *   label = @Translation("Downstream Quantity")
 * )
 */
class DownstreamQuantity extends TaskTypeBase {

  /**
   * Builds the field definitions for entities of this bundle.
   *
   * Important:
   * Field names must be unique across all bundles.
   * It is recommended to prefix them with the bundle name (plugin ID).
   *
   * @return \Drupal\entity\BundleFieldDefinition[]
   *   An array of bundle field definitions, keyed by field name.
   */
  public function buildFieldDefinitions() {
    $fields['downstream_quantity'] = BundleFieldDefinition::create('integer')
      ->setLabel(t('直接下游数量'))
      ->setDescription(t('任务完成的条件，完成的推广直接下游数量。'))
      ->setRequired(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 1)
      ->setDefaultValue(1)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_integer'
      ])
      ->setDisplayOptions('form', [
        'type' => 'number'
      ]);

    $fields['downstream_orders_total'] = BundleFieldDefinition::create('commerce_price')
      ->setLabel(t('总成交金额条件'))
      ->setDescription(t('只有达到此金额的下游，才能算作任务完成条件。'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default'
      ]);

    return $fields;
  }

  public function getDownstreamQuantity (TaskInterface $task) {
    if ($task->hasField('downstream_quantity')) {
      return (int)$task->get('downstream_quantity')->value;
    } else {
      return 1;
    }
  }

  public function getDownstreamOrdersTotal(TaskInterface $task) {
    if ($task->hasField('downstream_orders_total') && !$task->get('downstream_orders_total')->isEmpty()) {
      return $task->get('downstream_orders_total')->first()->toPrice();
    } else {
      return new Price('0.00', 'CNY');
    }
  }

  /**
   * 计算一个订单在一个任务中可获得的分数
   * @param TaskInterface $task
   * @param OrderInterface $commerce_order
   * @return float
   */
  public function computeScore(TaskInterface $task, OrderInterface $commerce_order) {
    // 如果用户的已有订单累加金额已经达到了条件金额，那么不要重复加分
    $old_orders_total = $this->getUserOrdersTotal($commerce_order->getCustomerId());
    if (!$old_orders_total->greaterThanOrEqual($this->getDownstreamOrdersTotal($task))) {
      // 查找用订单用户的所有订单，累加金额加上最新订单的金额如果达到条件金额，则加1分
      $new_orders_total = $old_orders_total->add($commerce_order->getTotalPrice());
      if ($new_orders_total->greaterThanOrEqual($this->getDownstreamOrdersTotal($task))) return 1;
    }
    return 0;
  }

  /**
   * 检查给定分数有否完成一个任务
   * @param TaskInterface $task
   * @param $score
   * @return bool
   */
  public function canCompleted(TaskInterface $task, $score) {
    if ($score >= $this->getDownstreamQuantity($task)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * 统计一个用户的所有有效订单总额
   * @param $user_id
   * @return Price
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getUserOrdersTotal($user_id) {
    /** @var Order[] $orders */
    $orders = \Drupal::entityTypeManager()->getStorage('commerce_order')->loadByProperties([
      'uid' => $user_id,
      'state' => 'completed'
    ]);

    $amount = null;
    foreach ($orders as $order) {
      if ($amount instanceof Price) $amount = $amount->add($order->getTotalPrice());
      else $amount = $order->getTotalPrice();
    }

    if ($amount === null) $amount = new Price('0.00', 'CNY');

    return $amount;
  }
}