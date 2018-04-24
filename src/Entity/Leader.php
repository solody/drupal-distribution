<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Leader entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_leader",
 *   label = @Translation("Leader"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\LeaderListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\LeaderViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\LeaderForm",
 *       "add" = "Drupal\distribution\Form\LeaderForm",
 *       "edit" = "Drupal\distribution\Form\LeaderForm",
 *       "delete" = "Drupal\distribution\Form\LeaderDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\LeaderAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\LeaderHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_leader",
 *   admin_permission = "administer leader entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_leader/{distribution_leader}",
 *     "add-form" = "/admin/distribution/distribution_leader/add",
 *     "edit-form" = "/admin/distribution/distribution_leader/{distribution_leader}/edit",
 *     "delete-form" = "/admin/distribution/distribution_leader/{distribution_leader}/delete",
 *     "collection" = "/admin/distribution/distribution_leader",
 *   },
 *   field_ui_base_route = "distribution_leader.settings"
 * )
 */
class Leader extends ContentEntityBase implements LeaderInterface
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
    public function getDistributor()
    {
        return $this->get('distributor_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['distributor_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('分销用户'))
            ->setSetting('target_type', 'distribution_distributor')
            ->setSetting('handler', 'default')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('领导名称'))
            ->setDefaultValue('')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string'
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield'
            ]);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('是否有效'))
            ->setDescription(t('如果要取消一个分销用户的团队领导资格，那么把此字段设置为 False.'))
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'boolean'
            ])
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
