<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Task entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_task",
 *   label = @Translation("Task"),
 *   bundle_label = @Translation("Task type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\TaskListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\TaskViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\TaskForm",
 *       "add" = "Drupal\distribution\Form\TaskForm",
 *       "edit" = "Drupal\distribution\Form\TaskForm",
 *       "delete" = "Drupal\distribution\Form\TaskDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\TaskAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\TaskHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_task",
 *   admin_permission = "administer task entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_task/{distribution_task}",
 *     "add-page" = "/admin/distribution/distribution_task/add",
 *     "add-form" = "/admin/distribution/distribution_task/add/{type}",
 *     "edit-form" = "/admin/distribution/distribution_task/{distribution_task}/edit",
 *     "delete-form" = "/admin/distribution/distribution_task/{distribution_task}/delete",
 *     "collection" = "/admin/distribution/distribution_task",
 *   },
 *   bundle_plugin_type = "task_type",
 *   field_ui_base_route = "entity.distribution_task_type.edit_form"
 * )
 */
class Task extends ContentEntityBase implements TaskInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool)$this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('编辑'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author'
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('任务名称'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield'
      ])
      ->setRequired(TRUE);

    $fields['reward'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('奖励金额'))
      ->setDescription(t('完成任务时，分销会员可以得到的一次性奖励金额。'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default'
      ]);

    $fields['cycle'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('任务周期（天）'))
      ->setDescription(t('任务完成的时间限制，超过时间限制后，任务未完成即为任务失败，设为0将不限'))
      ->setRequired(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 0)
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_integer'
      ])
      ->setDisplayOptions('form', [
        'type' => 'number'
      ]);

    $fields['newcomer'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('新手任务'))
      ->setDescription(t('新手任务会被新分销会员自动领取。'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean'
      ])
      ->setDisplayOptions('form', [
        'label' => 'inline',
        'type' => 'boolean_checkbox'
      ]);

    $fields['upgrade'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('完成任务后升级身份'))
      ->setDescription(t('完成任务后，分销会员可以升为高级分销会员。'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean'
      ])
      ->setDisplayOptions('form', [
        'label' => 'inline',
        'type' => 'boolean_checkbox'
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('是否启用'))
      ->setDescription(t('启用的任务才能被领取。'))
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
