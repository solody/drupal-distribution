<?php
namespace Drupal\distribution\Plugin\TaskType;

use Drupal\distribution\Plugin\TaskTypeBase;
use Drupal\entity\BundleFieldDefinition;

/**
 * @TaskType(
 *   id = "order_quantity",
 *   label = @Translation("Order Quantity")
 * )
 */
class OrderQuantity extends TaskTypeBase
{

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
    public function buildFieldDefinitions()
    {
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
}