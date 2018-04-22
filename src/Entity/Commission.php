<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Commission entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_commission",
 *   label = @Translation("Commission"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\CommissionListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\CommissionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\CommissionForm",
 *       "add" = "Drupal\distribution\Form\CommissionForm",
 *       "edit" = "Drupal\distribution\Form\CommissionForm",
 *       "delete" = "Drupal\distribution\Form\CommissionDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\CommissionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\CommissionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_commission",
 *   admin_permission = "administer commission entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "bundle" = "type",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_commission/{distribution_commission}",
 *     "add-form" = "/admin/distribution/distribution_commission/add",
 *     "edit-form" = "/admin/distribution/distribution_commission/{distribution_commission}/edit",
 *     "delete-form" = "/admin/distribution/distribution_commission/{distribution_commission}/delete",
 *     "collection" = "/admin/distribution/distribution_commission",
 *   },
 *   field_ui_base_route = "distribution_commission.settings",
 *   bundle_label = @Translation("Commission type"),
 *   bundle_plugin_type = "commission_type"
 * )
 */
class Commission extends ContentEntityBase implements CommissionInterface
{
    const TYPE_PROMOTION = 'promotion';
    const TYPE_CHAIN = 'chain';
    const TYPE_LEADER = 'leader';

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->get('name')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->set('name', $name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedTime($timestamp)
    {
        $this->set('created', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setValid($valid)
    {
        $this->set('status', $valid ? TRUE : FALSE);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['event_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('分佣事件'))
            ->setDescription(t('产生分佣项的分销事件。'))
            ->setSetting('target_type', 'distribution_event')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['distributor_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('收益者'))
            ->setDescription(t('在该分佣项中，获得收益的分销商。'))
            ->setSetting('target_type', 'distribution_distributor')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['name'] = BaseFieldDefinition::create('string_long')
            ->setLabel(t('标题'))
            ->setDescription(t('说明分佣项产生的原因。'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string'
            ]);

        $fields['amount'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('分得的金额'))
            ->setDescription(t('此分销商在此个分佣项中分得的金额。'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('是否有效'))
            ->setDescription(t('如果一个订单取消，需要同时取消佣金，那么把此字段设置为False。'))
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('view', [
                'type' => 'boolean'
            ]);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        return $fields;
    }

}
