<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldException;
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
class Leader extends ContentEntityBase implements LeaderInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    $distributor = $this->getDistributor();
    if ($this->get('state')->value === 'approved' && $this->get('status')->value) {
      $distributor->setIsLeader(true);
    } else {
      $distributor->setIsLeader(false);
    }
    $distributor->save();
  }

  public function delete() {
    $distributor = $this->getDistributor();
    $distributor->setIsLeader(false);
    $distributor->save();
    parent::delete();
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
  public function isActive() {
    return (bool)$this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', $active ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDistributor() {
    return $this->get('distributor_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['distributor_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('分销用户'))
      ->setSetting('target_type', 'distribution_distributor')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label'
      ])
      ->setDisplayOptions('form', [
        'type' => 'readonly_field_widget'
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('领导姓名'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield'
      ]);

    $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('手机号'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield'
      ]);

    $fields['qq'] = BaseFieldDefinition::create('string')
      ->setLabel(t('QQ号码'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield'
      ]);

    $fields['wechat'] = BaseFieldDefinition::create('string')
      ->setLabel(t('微信号'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield'
      ]);

    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('邮箱'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield'
      ]);

    $fields['address'] = BaseFieldDefinition::create('address')
      ->setLabel(t('通信地址'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'address_default'
      ])
      ->setDisplayOptions('form', [
        'type' => 'address_default'
      ]);

    $fields['apply_reason'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('申请理由'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea'
      ]);

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('审核状态'))
      ->setDescription(t('审核状态（待审核、已拒绝、已通过）。'))
      ->setRequired(TRUE)
      ->addConstraint('distribution_leader_chain_constraint')
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'state_transition_form'
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select'
      ])
      ->setSetting('workflow', 'distribution_leader_default');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('是否有效'))
      ->setDescription(t('如果要取消一个分销用户的团队领导资格，那么可以在此把分销商设置为无效，要恢复他的分销商资格，只需在此重新把其设置为有效。'))
      ->setDefaultValue(TRUE)
      ->addConstraint('distribution_leader_chain_constraint')
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
