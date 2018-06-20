<?php
namespace Drupal\distribution\Plugin\CommissionType;

use Drupal\distribution\Plugin\CommissionTypeBase;
use Drupal\entity\BundleFieldDefinition;

/**
 * 任务奖励
 * @CommissionType(
 *   id = "task",
 *   label = @Translation("Task")
 * )
 */
class Task extends CommissionTypeBase
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
        $fields['acceptance_id'] = BundleFieldDefinition::create('entity_reference')
            ->setLabel(t('完成的任务'))
            ->setSetting('target_type', 'distribution_acceptance')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        return $fields;
    }
}