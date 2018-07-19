<?php

namespace Drupal\distribution\Plugin\TaskType;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\distribution\Entity\TaskInterface;
use Drupal\distribution\Plugin\TaskTypeBase;
use Drupal\entity\BundleFieldDefinition;

/**
 * @TaskType(
 *   id = "order_quantity",
 *   label = @Translation("Order Quantity")
 * )
 */
class OrderQuantity extends TaskTypeBase {

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
    $fields['order_quantity'] = BundleFieldDefinition::create('integer')
      ->setLabel(t('订单数量'))
      ->setDescription(t('任务完成的条件，完成的推广订单数量。'))
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

    $fields['order_price'] = BundleFieldDefinition::create('commerce_price')
      ->setLabel(t('订单金额'))
      ->setDescription(t('只有达到此金额的订单，才能算作任务完成条件。'))
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

  public function getOrderQuantity (TaskInterface $task) {
    if ($task->hasField('order_quantity')) {
      return (int)$task->get('order_quantity')->velue;
    } else {
      return 1;
    }
  }

  public function getOrderPrice(TaskInterface $task) {
    if ($task->hasField('order_price') && !$task->get('order_price')->isEmpty()) {
      return $task->get('order_price')->first()->toPrice();
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
    if ($commerce_order->getTotalPrice()->greaterThanOrEqual($this->getOrderPrice($task))) {
      return 1;
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
    if ($score >= $this->getOrderQuantity($task)) {
      return true;
    } else {
      return false;
    }
  }
}