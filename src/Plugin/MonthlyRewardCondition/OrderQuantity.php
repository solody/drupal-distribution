<?php

namespace Drupal\distribution\Plugin\MonthlyRewardCondition;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Plugin\MonthlyRewardConditionBase;
use Drupal\finance\Entity\Ledger;
use Drupal\finance\FinanceManagerInterface;

/**
 * @MonthlyRewardCondition(
 *   id = "order_quantity",
 *   label = @Translation("Order Quantity")
 * )
 */
class OrderQuantity extends MonthlyRewardConditionBase {

  /**
   * @inheritdoc
   */
  public function elevateState(OrderInterface $order) {
    $distributor = $order->get('distributor')->entity;
    if ($distributor instanceof DistributorInterface) {
      // 如果订单金额达到所配置的条件，把订单金额记录到账户
      if ($order->getTotalPrice()->greaterThanOrEqual($this->configuration['order_price'])) {
        $this->getFinanceManager()->createLedger(
          $this->getDistributorAccount($distributor),
          Ledger::AMOUNT_TYPE_DEBIT,
           $order->getTotalPrice(),
          '订单['.$order->id().']达到了配置的条件标准',
          $order);
        return true;
      }
    }
    return false;
  }

  /**
   * @inheritdoc
   */
  public function evaluate(DistributorInterface $distributor, array $month) {
    // 查找某月份分销商所有的订单达标记录，如果数量达到了所配置的条件
    $query = \Drupal::entityQuery('finance_ledger');
    $query
      ->condition('account_id', $this->getDistributorAccount($distributor)->id())
      ->condition('created', (new \DateTime($month[0].'-'.$month[1].'-01 00:00:00'))->getTimestamp(), '>=')
      ->condition('created', (new \DateTime($month[0].'-'.($month[1] + 1).'-01 00:00:00'))->getTimestamp(), '<');

    $order_quantity = $query->count()->execute();
    if ($order_quantity >= $this->configuration['order_quantity']) {
      return true;
    } else {
      return false;
    }
  }

  private function getDistributorAccount(DistributorInterface $distributor) {
    $accounts = $this->getFinanceManager()->getAccountsByType('distribution_monthly_reward_condition_order_quantity');
    if (count($accounts)) {
      return array_pop($accounts);
    } else {
      // 奖金池账户
      return $this->getFinanceManager()->createAccount($distributor->getOwner(), 'distribution_monthly_reward_condition_order_quantity');
    }
  }

  /**
   * @return FinanceManagerInterface
   */
  private function getFinanceManager() {
    return \Drupal::getContainer()->get('finance.finance_manager');
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $amount = isset($this->configuration['order_price']) ? $this->configuration['order_price'] : ['number' => '', 'currency_code' => 'CNY'];
    $quantity = isset($this->configuration['order_quantity']) ? $this->configuration['order_quantity'] : 1;
    // An #ajax bug can cause $amount to be incomplete.
    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }

    $form['order_quantity'] = [
      '#type' => 'number',
      '#title' => t('Order quantity'),
      '#default_value' => $quantity,
      '#required' => TRUE,
      '#min' => 1
    ];
    $form['order_price'] = [
      '#type' => 'commerce_price',
      '#title' => t('Order price'),
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
      $this->configuration['order_price'] = $values['order_price'];
      $this->configuration['order_quantity'] = $values['order_quantity'];
    }
  }
}