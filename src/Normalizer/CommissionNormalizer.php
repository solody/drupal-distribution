<?php

namespace Drupal\distribution\Normalizer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\distribution\Entity\CommissionInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

class CommissionNormalizer extends ContentEntityNormalizer {

  public function supportsNormalization($data, $format = NULL) {
    if ($data instanceof EntityAdapter) {
      $entity =  $data->getValue();
      return $entity instanceof CommissionInterface;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity_adapter, $format = NULL, array $context = []) {
    $entity =  $entity_adapter->getValue();
    $data = parent::normalize($entity, $format, $context);

    if ($entity instanceof CommissionInterface) {
      $this->addCacheableDependency($context, $entity);

      $order = $entity->getEvent()->getOrder();
      if ($order instanceof OrderInterface) {
        $this->addCacheableDependency($context, $order);

        $data['_order_info'] = [
          'order_id' => $order->id(),
          'order_number' => $order->getOrderNumber(),
          'order_customer_name' => $order->getCustomer()->id()
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
