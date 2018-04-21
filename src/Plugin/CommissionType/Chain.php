<?php
namespace Drupal\distribution\Plugin\CommissionType;

use Drupal\distribution\Plugin\CommissionTypeBase;
use Drupal\entity\BundleFieldDefinition;

/**
 * @CommissionType(
 *   id = "chain",
 *   label = @Translation("Chain")
 * )
 */
class Chain extends CommissionTypeBase
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
        $fields['level_id'] = BundleFieldDefinition::create('entity_reference')
            ->setLabel(t('分佣链级设置'))
            ->setDescription(t('产生分佣项的分佣链级设置。'))
            ->setSetting('target_type', 'distribution_level')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        return $fields;
    }
}