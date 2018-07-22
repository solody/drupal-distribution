<?php

namespace Drupal\distribution\Plugin\MonthlyRewardStrategy;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormStateInterface;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Entity\MonthlyStatementInterface;
use Drupal\distribution\Plugin\MonthlyRewardStrategyBase;
use Drupal\finance\Entity\Ledger;
use Drupal\finance\Entity\LedgerInterface;
use Drupal\finance\FinanceManagerInterface;

/**
 * @MonthlyRewardStrategy(
 *   id = "three_level_achievement",
 *   label = @Translation("Three Level Achievement")
 * )
 */
class ThreeLevelAchievement extends MonthlyRewardStrategyBase {

  const ACHIEVEMENT_TYPE_INSIDE = 'inside';
  const ACHIEVEMENT_TYPE_OUTSIDE = 'outside';

  static $globalAchievementInside = [];
  static $globalAchievementOutside = [];

  /**
   * @inheritdoc
   */
  public function elevateState(OrderInterface $order) {
    $distributor = $order->get('distributor')->entity;
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

    $rate_inside = $distributor_achievement_inside / (float)$this->getGlobalAchievement($month, self::ACHIEVEMENT_TYPE_INSIDE)->getNumber();
    $rate_outside = $distributor_achievement_outside / (float)$this->getGlobalAchievement($month, self::ACHIEVEMENT_TYPE_INSIDE)->getNumber();

    $amount = $statement->getRewardTotal();

    if (!$amount->isZero() && $rate_inside > 0) $this->createCommission($statement, $distributor, $amount, '（3级内业绩率奖励）');
    if (!$amount->isZero() && $rate_outside > 0) $this->createCommission($statement, $distributor, $amount, '（3级外业绩率奖励）');

    return $amount;
  }

  private function getGlobalAchievement(array $month, $type) {
    $key = $type.$month[0].$month[1];
    if (!isset(self::$globalAchievementInside[$key])) {
      self::$globalAchievementInside[$key];
    }
    return self::$globalAchievementInside[$key];
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
      ->condition('account_id.entity:finance_account.type.target_id', $account_type_id)
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
    $accounts = $this->getFinanceManager()->getAccountsByType('distribution_tla_inside');
    if (count($accounts)) {
      return array_pop($accounts);
    } else {
      return $this->getFinanceManager()->createAccount($distributor->getOwner(), 'distribution_tla_inside');
    }
  }

  private function getDistributorOutsideAccount(DistributorInterface $distributor) {
    $accounts = $this->getFinanceManager()->getAccountsByType('distribution_tla_outside');
    if (count($accounts)) {
      return array_pop($accounts);
    } else {
      return $this->getFinanceManager()->createAccount($distributor->getOwner(), 'distribution_tla_outside');
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