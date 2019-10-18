<?php

namespace Drupal\distribution\Plugin\MonthlyRewardStrategy;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormStateInterface;
use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Entity\MonthlyStatementInterface;
use Drupal\distribution\Plugin\MonthlyRewardStrategyBase;
use Drupal\account\Entity\AccountInterface;
use Drupal\account\Entity\Ledger;
use Drupal\account\Entity\LedgerInterface;
use Drupal\account\FinanceManagerInterface;

/**
 * @MonthlyRewardStrategy(
 *   id = "three_level_achievement",
 *   label = @Translation("Three Level Achievement")
 * )
 */
class ThreeLevelAchievement extends MonthlyRewardStrategyBase {

  const ACHIEVEMENT_TYPE_INSIDE = 'inside';
  const ACHIEVEMENT_TYPE_OUTSIDE = 'outside';

  static $globalAchievement = [];

  /**
   * @inheritdoc
   */
  public function elevateState(OrderInterface $order) {
    $distributor = $order->get('distributor')->entity;

    if ($distributor instanceof DistributorInterface) { // 非会员被推广购买的订单 或 会员购买的订单
      if ($order->getCustomerId() !== $distributor->getOwnerId()) {
        // 非会员被推广购买的订单
        // 本订单算作购买者3级内业绩
        $distributor = \Drupal::getContainer()->get('distribution.distribution_manager')->getDistributor($order->getCustomer());
      }
    } else {
      // 非会员自主购买订单
      // 本订单算作购买者3级内业绩
      $distributor = \Drupal::getContainer()->get('distribution.distribution_manager')->getDistributor($order->getCustomer());
    }

    if ($distributor instanceof DistributorInterface) {

      $current_distributor = $distributor;
      $current_distributor_level = 0;

      do {
        $account = null;
        $remarks = '';
        if ($current_distributor_level <= 3) {
          $account = $this->getDistributorInsideAccount($current_distributor);
          $remarks = '订单['.$order->id().']属于当前分销用户3级以内的业绩';
        } else {
          $account = $this->getDistributorOutsideAccount($current_distributor);
          $remarks = '订单['.$order->id().']属于当前分销用户3级以外的业绩';
        }

        $this->getFinanceManager()->createLedger(
          $account,
          Ledger::AMOUNT_TYPE_DEBIT,
          $order->getTotalPrice(),
          $remarks,
          $order);

        $current_distributor = $current_distributor->getUpstreamDistributor();
        $current_distributor_level++;
      } while($current_distributor instanceof DistributorInterface);
    }
  }

  /**
   * @inheritdoc
   * @throws \Exception
   */
  public function assignReward(DistributorInterface $distributor, array $month, MonthlyStatementInterface $statement) {
    // 计算业绩率
    $distributor_achievement_inside = (float)$this->computeAchievement($distributor, $month, self::ACHIEVEMENT_TYPE_INSIDE)->getNumber();
    $distributor_achievement_outside = (float)$this->computeAchievement($distributor, $month, self::ACHIEVEMENT_TYPE_OUTSIDE)->getNumber();

    if ($distributor_achievement_inside <= 0) $rate_inside = 0;
    else $rate_inside = $distributor_achievement_inside / (float)$this->getGlobalAchievement($month, self::ACHIEVEMENT_TYPE_INSIDE)->getNumber();

    if ($distributor_achievement_outside <= 0) $rate_outside = 0;
    else $rate_outside = $distributor_achievement_outside / (float)$this->getGlobalAchievement($month, self::ACHIEVEMENT_TYPE_OUTSIDE)->getNumber();

    $amount = $statement->getRewardTotal();
    $assigned_amount = new Price('0.00', $amount->getCurrencyCode());

    if (!$amount->isZero() && $rate_inside > 0) {
      $amount_inside = $amount->multiply((string)$this->configuration['percentage_inside'])->multiply('0.01')->multiply((string)$rate_inside);
      $fix_amount_number = floor((float)$amount_inside->getNumber() * 100) / 100;
      $amount_inside = new Price((string)$fix_amount_number, $amount_inside->getCurrencyCode());

      if (!$amount_inside->isZero()) {
        $this->createCommission($statement, $distributor, $amount_inside, '（3级内业绩率奖励，计算方法：'.$amount->getCurrencyCode().$amount->getNumber().' x '.$this->configuration['percentage_inside'].'% x '.$rate_inside.'）');
        $assigned_amount = $assigned_amount->add($amount_inside);
      }
    }
    if (!$amount->isZero() && $rate_outside > 0) {
      $amount_outside = $amount->multiply((string)$this->configuration['percentage_outside'])->multiply('0.01')->multiply((string)$rate_outside);
      $fix_amount_number = floor((float)$amount_outside->getNumber() * 100) / 100;
      $amount_outside = new Price((string)$fix_amount_number, $amount_outside->getCurrencyCode());

      if (!$amount_outside->isZero()) {
        $this->createCommission($statement, $distributor, $amount_outside, '（3级外业绩率奖励，计算方法：'.$amount->getCurrencyCode().$amount->getNumber().' x '.$this->configuration['percentage_outside'].'% x '.$rate_outside.'）');
        $assigned_amount = $assigned_amount->add($amount_outside);
      }
    }

    return $assigned_amount;
  }

  /**
   * @param array $month
   * @param $type
   * @return mixed
   * @throws \Exception
   */
  private function getGlobalAchievement(array $month, $type) {
    $key = $type.$month[0].$month[1];

    if (!isset(self::$globalAchievement[$key])) {
      self::$globalAchievement[$key] = $this->computeGlobalAchievement($month, $type);
    }
    return self::$globalAchievement[$key];
  }

  /**
   * 计算所有分销用户某月的业绩
   * @param array $month
   * @param $type
   * @return Price
   * @throws \Exception
   */
  private function computeGlobalAchievement(array $month, $type) {
    $account_type_id = '';
    switch ($type) {
      case self::ACHIEVEMENT_TYPE_INSIDE:
        $account_type_id = 'distribution_tla_inside';
        break;
      case self::ACHIEVEMENT_TYPE_OUTSIDE:
        $account_type_id = 'distribution_tla_outside';
        break;
      default:
        throw new \Exception('unknown ACHIEVEMENT_TYPE');
    }

    $query = \Drupal::entityQuery('finance_ledger');
    $query
      ->condition('account_id.entity:account.type.target_id', $account_type_id)
      ->condition('created', (new \DateTime($month[0].'-'.$month[1].'-01 00:00:00'))->getTimestamp(), '>=')
      ->condition('created', (new \DateTime($month[0].'-'.($month[1] + 1).'-01 00:00:00'))->getTimestamp(), '<');

    $ids = $query->execute();
    $amount = new Price('0.00', 'CNY');
    if (count($ids)) {
      $ledgers = Ledger::loadMultiple($ids);
      foreach ($ledgers as $ledger) {
        if ($ledger instanceof LedgerInterface) {
          if ($ledger->getAmountType() === Ledger::AMOUNT_TYPE_DEBIT) $amount = $amount->add($ledger->getAmount());
          if ($ledger->getAmountType() === Ledger::AMOUNT_TYPE_CREDIT) $amount = $amount->subtract($ledger->getAmount());
        }
      }
    }

    return $amount;
  }

  /**
   * 计算某个分销用户某月的业绩
   * @param DistributorInterface $distributor
   * @param array $month
   * @param $type
   * @return Price
   * @throws \Exception
   */
  private function computeAchievement(DistributorInterface $distributor, array $month, $type) {
    $account = null;
    switch ($type) {
      case self::ACHIEVEMENT_TYPE_INSIDE:
        $account = $this->getDistributorInsideAccount($distributor);
        break;
      case self::ACHIEVEMENT_TYPE_OUTSIDE:
        $account = $this->getDistributorOutsideAccount($distributor);
        break;
      default:
        throw new \Exception('unknown ACHIEVEMENT_TYPE');
    }

    $query = \Drupal::entityQuery('finance_ledger');
    $query
      ->condition('account_id', $account->id())
      ->condition('created', (new \DateTime($month[0].'-'.$month[1].'-01 00:00:00'))->getTimestamp(), '>=')
      ->condition('created', (new \DateTime($month[0].'-'.($month[1] + 1).'-01 00:00:00'))->getTimestamp(), '<');

    $ids = $query->execute();
    $amount = new Price('0.00', 'CNY');
    if (count($ids)) {
      $ledgers = Ledger::loadMultiple($ids);
      foreach ($ledgers as $ledger) {
        if ($ledger instanceof LedgerInterface) {
          if ($ledger->getAmountType() === Ledger::AMOUNT_TYPE_DEBIT) $amount = $amount->add($ledger->getAmount());
          if ($ledger->getAmountType() === Ledger::AMOUNT_TYPE_CREDIT) $amount = $amount->subtract($ledger->getAmount());
        }
      }
    }

    return $amount;
  }

  private function getDistributorInsideAccount(DistributorInterface $distributor) {
    $account = $this->getFinanceManager()->getAccount($distributor->getOwner(), 'distribution_tla_inside');
    if ($account instanceof AccountInterface) {
      return $account;
    } else {
      return $this->getFinanceManager()->createAccount($distributor->getOwner(), 'distribution_tla_inside');
    }
  }

  private function getDistributorOutsideAccount(DistributorInterface $distributor) {
    $account = $this->getFinanceManager()->getAccount($distributor->getOwner(), 'distribution_tla_outside');
    if ($account instanceof AccountInterface) {
      return $account;
    } else {
      return $this->getFinanceManager()->createAccount($distributor->getOwner(), 'distribution_tla_outside');
    }
  }

  /**
   * @return FinanceManagerInterface
   */
  private function getFinanceManager() {
    return \Drupal::getContainer()->get('account.finance_manager');
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $percentage_inside = isset($this->configuration['percentage_inside']) ? $this->configuration['percentage_inside'] : 0;
    $percentage_outside = isset($this->configuration['percentage_outside']) ? $this->configuration['percentage_outside'] : 0;

    $form['percentage_inside'] = [
      '#type' => 'number',
      '#title' => t('3级以内奖金基数比例'),
      '#default_value' => $percentage_inside,
      '#required' => TRUE,
      '#field_suffix' => '%',
      '#min' => 0,
      '#max' => 100,
      '#step' => 1
    ];
    $form['percentage_outside'] = [
      '#type' => 'number',
      '#title' => t('3级以外奖金基数比例'),
      '#default_value' => $percentage_outside,
      '#required' => TRUE,
      '#field_suffix' => '%',
      '#min' => 0,
      '#max' => 100,
      '#step' => 1
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
      $this->configuration['percentage_inside'] = $values['percentage_inside'];
      $this->configuration['percentage_outside'] = $values['percentage_outside'];
    }
  }
}
