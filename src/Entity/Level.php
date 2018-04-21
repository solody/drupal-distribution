<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Level entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_level",
 *   label = @Translation("Level"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\LevelListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\LevelViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\LevelForm",
 *       "add" = "Drupal\distribution\Form\LevelForm",
 *       "edit" = "Drupal\distribution\Form\LevelForm",
 *       "delete" = "Drupal\distribution\Form\LevelDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\LevelAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\LevelHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_level",
 *   admin_permission = "administer level entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_level/{distribution_level}",
 *     "add-form" = "/admin/distribution/distribution_level/add",
 *     "edit-form" = "/admin/distribution/distribution_level/{distribution_level}/edit",
 *     "delete-form" = "/admin/distribution/distribution_level/{distribution_level}/delete",
 *     "collection" = "/admin/distribution/distribution_level",
 *   },
 *   field_ui_base_route = "distribution_level.settings"
 * )
 */
class Level extends ContentEntityBase implements LevelInterface
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
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['target_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('分销标的'))
            ->setSetting('target_type', 'distribution_target')
            ->setSetting('handler', 'default')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['level_number'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('向上分佣的级数'))
            ->setReadOnly(TRUE)
            ->setSetting('unsigned', TRUE)
            ->setSetting('min', 1)
            ->setDisplayOptions('view', [
                'type' => 'number_integer'
            ]);

        $fields['percentage'] = BaseFieldDefinition::create('decimal')
            ->setLabel(t('分佣比例'))
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

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('是否有效'))
            ->setDescription(t('如果要取消一个商品的某个链级的分佣，那么把此字段设置为 False.'))
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
