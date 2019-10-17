<?php

namespace Drupal\distribution\Normalizer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\distribution\DistributionManager;
use Drupal\distribution\Entity\CommissionInterface;
use Drupal\account\Entity\Ledger;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

class CommissionNormalizer extends ContentEntityNormalizer {

  public function supportsNormalization($data, $format = NULL) {
    return $data instanceof CommissionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $data = parent::normalize($entity, $format, $context);

    if ($entity instanceof CommissionInterface) {
      $this->addCacheableDependency($context, $entity);

      // 检查到账状态和到账时间
      $ledgers = \Drupal::entityTypeManager()->getStorage('finance_ledger')->loadByProperties([
        'source__target_id' => $entity->id(),
        'source__target_type' => $entity->getEntityTypeId()
      ]);

      $data['_finance_status'] = [
        'valid' => false,
        'time' => null
      ];

      foreach ($ledgers as $ledger) {
        /** @var Ledger $ledger */
        if ($ledger->getAccountType() === DistributionManager::FINANCE_ACCOUNT_TYPE) {
          $data['_finance_status']['valid'] = true;
          $data['_finance_status']['time'] = $ledger->getCreatedTime();
        }
      }

      $order = $entity->getEvent()->getOrder();
      if ($order instanceof OrderInterface) {
        $this->addCacheableDependency($context, $order);

        $data['_order_info'] = [
          'order_id' => $order->id(),
          'order_number' => $order->getOrderNumber(),
          'order_amount' => $order->getTotalPrice()->toArray(),
          'order_customer_name' => $order->getCustomer()->getAccountName()
        ];
        $purchased_entity = $order->getItems()[0]->getPurchasedEntity();
        if (method_exists($purchased_entity, 'getProduct')) {
          $product = $purchased_entity->getProduct();
          if ($product instanceof ProductInterface) {
            $this->addCacheableDependency($context, $product);

            $data['_order_info']['product_id'] = $product->id();
            $data['_order_info']['product_title'] = $product->getTitle();
          }
        }
      }
    }

    return $data;
  }

}
