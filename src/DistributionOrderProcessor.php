<?php

namespace Drupal\distribution;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\distribution\Entity\Commission;

/**
 * 执行分销优惠价格调整
 */
class DistributionOrderProcessor implements OrderProcessorInterface {
  /**
   * @var DistributionManager
   */
  protected $distributionManager;

  /**
   * Constructs a new DistributionOrderProcessor object.
   *
   * @param DistributionManager $distribution_manager
   */
  public function __construct(DistributionManager $distribution_manager) {
    $this->distributionManager = $distribution_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    $config = \Drupal::config('distribution.settings');

    if ($config->get('enable')) {
      $distributor = $this->distributionManager->determineDistributor($order);

      if ($distributor) {
        foreach ($order->getItems() as $orderItem) {
          $target = $this->distributionManager->getTarget($orderItem->getPurchasedEntity());

          // 如果没有设置分销数据，跳过
          if (!$target) continue;

          $unit_price = $orderItem->getAdjustedUnitPrice();
          $adjustment_amount = $target->getAmountOff();

          if ($config->get('enable_amount_off') && $target && $adjustment_amount) {
            // 执行分销优惠价格调整
            if ($unit_price->getCurrencyCode() != $adjustment_amount->getCurrencyCode()) {
              continue;
            }
            // Don't reduce the order item unit price past zero.
            if ($adjustment_amount->greaterThan($unit_price)) {
              $adjustment_amount = $unit_price;
            }

            $orderItem->addAdjustment(new Adjustment([
              'type' => 'distribution_amount_off',
              // @todo Change to label from UI when added in #2770731.
              'label' => t('分销优惠'),
              'amount' => $adjustment_amount->multiply('-1'),
              'source_id' => $target->id(),
            ]));
          }

          if ($config->get('commission.chain') && $config->get('chain_commission.enable_distributor_self_commission') && $target) {

            $customer_distributor = $this->distributionManager->getDistributor($order->getCustomer());
            if ($customer_distributor) {
              // 购买者本身是分销用户，把1级佣金作为价格调整
              $chain_commission_amount = $this->distributionManager->computeCommissionAmount($target, Commission::TYPE_CHAIN, $orderItem->getAdjustedUnitPrice());
              $amount = new Price((string)($chain_commission_amount->getNumber() * $config->get('chain_commission.level_1') / 100), $chain_commission_amount->getCurrencyCode());

              $unit_price = $orderItem->getAdjustedUnitPrice();
              $adjustment_amount = $amount;

              if ($unit_price->getCurrencyCode() != $adjustment_amount->getCurrencyCode()) {
                continue;
              }
              // Don't reduce the order item unit price past zero.
              if ($adjustment_amount->greaterThan($unit_price)) {
                $adjustment_amount = $unit_price;
              }

              $orderItem->addAdjustment(new Adjustment([
                'type' => 'distribution_commission_off',
                // @todo Change to label from UI when added in #2770731.
                'label' => t('分销用户佣金直抵'),
                'amount' => $adjustment_amount->multiply('-1'),
                'source_id' => $target->id(),
              ]));
            }
          }
        }
      }
    }
  }

}
