<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Acceptance entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_acceptance",
 *   label = @Translation("Acceptance"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\AcceptanceListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\AcceptanceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\AcceptanceForm",
 *       "add" = "Drupal\distribution\Form\AcceptanceForm",
 *       "edit" = "Drupal\distribution\Form\AcceptanceForm",
 *       "delete" = "Drupal\distribution\Form\AcceptanceDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\AcceptanceAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\AcceptanceHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_acceptance",
 *   admin_permission = "administer acceptance entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_acceptance/{distribution_acceptance}",
 *     "add-form" = "/admin/distribution/distribution_acceptance/add",
 *     "edit-form" = "/admin/distribution/distribution_acceptance/{distribution_acceptance}/edit",
 *     "delete-form" = "/admin/distribution/distribution_acceptance/{distribution_acceptance}/delete",
 *     "collection" = "/admin/distribution/distribution_acceptance",
 *   },
 *   field_ui_base_route = "distribution_acceptance.settings"
 * )
 */
class Acceptance extends ContentEntityBase implements AcceptanceInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
    }

    public function preSave(EntityStorageInterface $storage)
    {
        parent::preSave($storage);
        // TODO:: 检查成绩是否达到任务完成标准，设置完成状态
        if (!$this->get('status')->value) {
            /** @var Task $task */
            $task = $this->get('task_id')->entity;
        }
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
    public function isCompleted()
    {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setCompleted($completed)
    {
        $this->set('status', $completed ? TRUE : FALSE);
        return $this;
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

        $fields['task_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('领取的任务'))
            ->setSetting('target_type', 'distribution_task')
            ->setSetting('handler', 'default')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['achievement'] = BaseFieldDefinition::create('float')
            ->setLabel(t('任务成绩得分总计'))
            ->setRequired(TRUE)
            ->setSetting('unsigned', TRUE)
            ->setSetting('min', 0)
            ->setDefaultValue(0)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'number_decimal'
            ])
            ->setDisplayOptions('form', [
                'type' => 'number'
            ]);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('已完成'))
            ->setDefaultValue(false)
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
