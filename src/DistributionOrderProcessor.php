<?php

namespace Drupal\distribution;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;

/**
 * 执行分销优惠价格调整
 */
class DistributionOrderProcessor implements OrderProcessorInterface
{
    /**
     * @var DistributionManager
     */
    protected $distributionManager;

    /**
     * Constructs a new DistributionOrderProcessor object.
     *
     * @param DistributionManager $distribution_manager
     */
    public function __construct(DistributionManager $distribution_manager)
    {
        $this->distributionManager = $distribution_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(OrderInterface $order)
    {
        $config = \Drupal::config('distribution.settings');

        if ($config->get('enable')) {
            $distributor = $this->distributionManager->determineDistributor($order);

            if ($distributor) {
                foreach ($order->getItems() as $orderItem) {
                    $target = $this->distributionManager->getTarget($orderItem->getPurchasedEntity());

                    $unit_price = $orderItem->getUnitPrice();
                    $adjustment_amount = $target->getAmountOff();

                    if ($target && $adjustment_amount) {
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
                }
            }
        }
    }

}
