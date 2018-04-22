<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Distributor entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_distributor",
 *   label = @Translation("Distributor"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\DistributorListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\DistributorViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\DistributorForm",
 *       "add" = "Drupal\distribution\Form\DistributorForm",
 *       "edit" = "Drupal\distribution\Form\DistributorForm",
 *       "delete" = "Drupal\distribution\Form\DistributorDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\DistributorAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\DistributorHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_distributor",
 *   admin_permission = "administer distributor entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_distributor/{distribution_distributor}",
 *     "add-form" = "/admin/distribution/distribution_distributor/add",
 *     "edit-form" = "/admin/distribution/distribution_distributor/{distribution_distributor}/edit",
 *     "delete-form" = "/admin/distribution/distribution_distributor/{distribution_distributor}/delete",
 *     "collection" = "/admin/distribution/distribution_distributor",
 *   },
 *   field_ui_base_route = "distribution_distributor.settings"
 * )
 */
class Distributor extends ContentEntityBase implements DistributorInterface
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
    public function getOwner()
    {
        return $this->get('user_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->get('user_id')->target_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->set('user_id', $account->id());
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
     * @inheritdoc
     */
    public function getLevelNumber()
    {
        return $this->get('level_number')->value;
    }

    /**
     * @inheritdoc
     */
    public function getUpstreamDistributor()
    {
        return $this->get('upstream_distributor_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('分销用户'))
            ->setSetting('target_type', 'user')
            ->setSetting('handler', 'default')
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('分销商名称'))
            ->setDefaultValue('')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string'
            ]);

        $fields['logo'] = BaseFieldDefinition::create('image')
            ->setLabel(t('分销商LOGO'))
            ->setCardinality(1)
            ->setSettings([
                'file_directory' => 'distribution/distributor/logo/[date:custom:Y]-[date:custom:m]',
                'file_extensions' => 'png gif jpg jpeg',
                'max_filesize' => '200 KB',
                'max_resolution' => '',
                'min_resolution' => '',
                'alt_field' => false,
                'alt_field_required' => true,
                'title_field' => false,
                'title_field_required' => false,
                'handler' => 'default:file',
                'handler_settings' => []
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'image'
            ])
            ->setDisplayOptions('form', [
                'type' => 'image_image'
            ]);

        $fields['enable_distributor_brand'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('是否启用分销商品牌形象'))
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('view', [
                'type' => 'boolean'
            ])
            ->setDisplayOptions('form', [
                'type' => 'boolean_checkbox'
            ]);

        $fields['agent_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('分销商的真实姓名'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string'
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield'
            ]);

        $fields['agent_phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('分销商的联系手机'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string'
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield'
            ]);

        $fields['upstream_distributor_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('上游分销商'))
            ->setSetting('target_type', 'distribution_distributor')
            ->setSetting('handler', 'default')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['level_number'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('分销商的级数'))
            ->setDescription(t('分销商处于分销链中的级层数，没有上游分销商的为1级。'))
            ->setReadOnly(TRUE)
            ->setSetting('unsigned', TRUE)
            ->setSetting('min', 1)
            ->setDefaultValue(1)
            ->setDisplayOptions('view', [
                'type' => 'number_integer'
            ]);

        $fields['amount_achievement'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('分销商业绩总额'))
            ->setDescription(t('分销商的所有下线发生的购买订单金额累计。'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['amount_leader'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('获得的领导分成总额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['amount_chain'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('获得的链级分佣总额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['amount_promotion'] = BaseFieldDefinition::create('commerce_price')
            ->setLabel(t('获得的推广佣金总额'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'commerce_price_default'
            ]);

        $fields['state'] = BaseFieldDefinition::create('state')
            ->setLabel(t('审核状态'))
            ->setDescription(t('此分销商的审核状态（待审核、已拒绝、已通过）。'))
            ->setRequired(TRUE)
            ->setSetting('max_length', 255)
            ->setDisplayOptions('view', [
                'label' => 'hidden',
                'type' => 'state_transition_form'
            ])
            ->setDisplayOptions('form', [
                'type' => 'options_select'
            ])
            ->setSetting('workflow', 'distribution_distributor_default');

        $fields['approved_time'] = BaseFieldDefinition::create('timestamp')
            ->setLabel(t('审核通过时间'))
            ->setDescription(t('此分销商审核通过的时间。'))
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'timestamp',
                'weight' => 0,
            ]);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('是否启用'))
            ->setDescription(t('关闭后，此分销商节点以及其下的所有分销商节点，都不会再产生分佣。'))
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'boolean'
            ])
            ->setDisplayOptions('form', [
                'label' => 'inline',
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
