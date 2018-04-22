<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Target entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_target",
 *   label = @Translation("Target"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\TargetListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\TargetViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\TargetForm",
 *       "add" = "Drupal\distribution\Form\TargetForm",
 *       "edit" = "Drupal\distribution\Form\TargetForm",
 *       "delete" = "Drupal\distribution\Form\TargetDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\TargetAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\TargetHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_target",
 *   admin_permission = "administer target entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_target/{distribution_target}",
 *     "add-form" = "/admin/distribution/distribution_target/add",
 *     "edit-form" = "/admin/distribution/distribution_target/{distribution_target}/edit",
 *     "delete-form" = "/admin/distribution/distribution_target/{distribution_target}/delete",
 *     "collection" = "/admin/distribution/distribution_target",
 *   },
 *   field_ui_base_route = "distribution_target.settings"
 * )
 */
class Target extends ContentEntityBase implements TargetInterface
{

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
    public function isActive()
    {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setActive($active)
    {
        $this->set('status', $active ? TRUE : FALSE);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAmountPromotion()
    {
        if (!$this->get('amount_promotion')->isEmpty()) {
            return $this->get('amount_promotion')->first()->toPrice();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAmountChain()
    {
        if (!$this->get('amount_chain')->isEmpty()) {
            return $this->get('amount_chain')->first()->toPrice();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAmountLeader()
    {
        if (!$this->get('amount_leader')->isEmpty()) {
            return $this->get('amount_leader')->first()->toPrice();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPercentagePromotion()
    {
        return $this->get('percentage_promotion')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getPercentageChain()
    {
        return $this->get('percentage_chain')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getPercentageLeader()
    {
        return $this->get('percentage_leader')->value;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);


        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('商品名称'))
            ->setDefaultValue('')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string'
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield'
            ]);

        $fields['purchasable_entity'] = BaseFieldDefinition::create('dynamic_entity_reference')
            ->setLabel('可购买实体')
            ->setCardinality(1)
            ->setDisplayOptions('form', [
                'type' => 'dynamic_entity_reference_default'
            ])
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'dynamic_entity_reference_label'
            ]);

        $fields['amount_off'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('产品价格中的分销优惠金额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['percentage_leader'] = BaseFieldDefinition::create('decimal')
            ->setLabel(t('产品价格中的团队领导佣金比例'))
            ->setSettings([
                'min' => '0.00',
                'max' => '100.00',
                'suffix' => '%',
                'precision' => 5,
                'scale' => 2,
            ])
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'number_decimal'
            ])
            ->setDisplayOptions('form', [
                'type' => 'number'
            ]);

        $fields['percentage_chain'] = BaseFieldDefinition::create('decimal')
            ->setLabel(t('产品价格中的链级佣金比例'))
            ->setSettings([
                'min' => '0.00',
                'max' => '100.00',
                'suffix' => '%',
                'precision' => 5,
                'scale' => 2,
            ])
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'number_decimal'
            ])
            ->setDisplayOptions('form', [
                'type' => 'number'
            ]);

        $fields['percentage_promotion'] = BaseFieldDefinition::create('decimal')
            ->setLabel(t('产品价格中的推广佣金比例'))
            ->setSettings([
                'min' => '0.00',
                'max' => '100.00',
                'suffix' => '%',
                'precision' => 5,
                'scale' => 2,
            ])
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'number_decimal'
            ])
            ->setDisplayOptions('form', [
                'type' => 'number'
            ]);

        $fields['amount_leader'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('产品价格中的团队领导佣金金额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['amount_chain'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('产品价格中的链级佣金金额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['amount_promotion'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('产品价格中的推广佣金金额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('是否启用'))
            ->setDescription(t('如果希望取消一个商品的分销佣金，那么可以把此字段设置为 False。'))
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('form', [
                'type' => 'boolean_checkbox'
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
