<?php

namespace Drupal\distribution\Plugin\MonthlyRewardCondition;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormStateInterface;
use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Plugin\MonthlyRewardConditionBase;

/**
 * @MonthlyRewardCondition(
 *   id = "downstream_quantity",
 *   label = @Translation("Downstream Quantity")
 * )
 */
class DownstreamQuantity extends MonthlyRewardConditionBase {

  /**
   * @inheritdoc
   */
  public function elevateState(OrderInterface $order) {
    // 不需要记录额外的状态
    return false;
  }

  /**
   * @inheritdoc
   */
  public function evaluate(DistributorInterface $distributor, array $month) {
    // 查找所设定级数内的所有下游
    $distributors = Distributor::loadMultiple();

    $getDownstream = function ($upstream_distributor = null) use ($distributors) {
      $rs = [];
      foreach ($distributors as $distributor) {
        /** @var Distributor $distributor */
        if ($upstream_distributor instanceof Distributor) {
          if ($distributor->getUpstreamDistributor() instanceof Distributor && $distributor->getUpstreamDistributor()->id() === $upstream_distributor->id()) {
            $rs[] = $distributor;
          }
        } else {
          if (empty($distributor->getUpstreamDistributor())) {
            $rs[] = $distributor;
          }
        }
      }
      return $rs;
    };

    $current_level = 1;
    $current_upstream_distributor = $distributor;
    $current_distributors = $getDownstream($current_upstream_distributor);

    $all_distributors = $current_distributors;

    while (count($current_distributors)) {
      if ($current_level > (int)$this->configuration['downstream_level']) break;

      $current_level++;
      $new_current_distributors = [];
      foreach ($current_distributors as $current_distributor) {
        $new_current_distributors = array_merge($new_current_distributors, $getDownstream($current_distributor));
      }

      $current_distributors = $new_current_distributors;
      $all_distributors = array_merge($all_distributors, $current_distributors);
    };

    if (count($all_distributors) < (int)$this->configuration['downstream_quantity']) {
      // 数量上没达到要求
      return false;
    } else {
      // 进一步检查每个分销商用户的订单成交总额
      /** @var Order[] $orders */
      $orders = \Drupal::entityTypeManager()->getStorage('commerce_order')->loadByProperties([
        'state' => 'completed'
      ]);

      $downstream_quantity = 0;
      $downstream_orders_total = new Price('0.00', 'CNY');
      if (isset($this->configuration['downstream_orders_total'])) {
        $downstream_orders_total = new Price($this->configuration['downstream_orders_total']['number'], $this->configuration['downstream_orders_total']['currency_code']);
      }

      foreach ($all_distributors as $downstream_distributor) {
        /** @var Distributor $downstream_distributor */
        // 计算单个分销商的成交订单总额
        $distributor_orders_total = $this->getUserOrdersTotal($downstream_distributor->getOwnerId());

        if ($distributor_orders_total instanceof Price && $distributor_orders_total->greaterThanOrEqual($downstream_orders_total)) $downstream_quantity++;
      }

      if ($downstream_quantity < (int)$this->configuration['downstream_quantity']) return false;
      else return true;
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

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $amount = isset($this->configuration['downstream_orders_total']) ? $this->configuration['downstream_orders_total'] : ['number' => '0.00', 'currency_code' => 'CNY'];
    $quantity = isset($this->configuration['downstream_quantity']) ? $this->configuration['downstream_quantity'] : 1;
    $level = isset($this->configuration['downstream_level']) ? $this->configuration['downstream_level'] : 1;
    // An #ajax bug can cause $amount to be incomplete.
    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }

    $form['downstream_quantity'] = [
      '#type' => 'number',
      '#title' => t('下游数量'),
      '#default_value' => $quantity,
      '#required' => TRUE,
      '#min' => 1
    ];
    $form['downstream_level'] = [
      '#type' => 'number',
      '#title' => t('计入下游数量的级数范围'),
      '#default_value' => $level,
      '#required' => TRUE,
      '#min' => 1
    ];
    $form['downstream_orders_total'] = [
      '#type' => 'commerce_price',
      '#title' => t('总成交金额条件'),
      '#default_value' => $amount,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @inheritdoc
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['downstream_orders_total'] = $values['downstream_orders_total'];
      $this->configuration['downstream_quantity'] = $values['downstream_quantity'];
      $this->configuration['downstream_level'] = $values['downstream_level'];
    }
  }
}