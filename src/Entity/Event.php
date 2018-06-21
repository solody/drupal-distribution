<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Event entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_event",
 *   label = @Translation("Event"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\EventListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\EventViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\EventForm",
 *       "add" = "Drupal\distribution\Form\EventForm",
 *       "edit" = "Drupal\distribution\Form\EventForm",
 *       "delete" = "Drupal\distribution\Form\EventDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\EventAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\EventHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_event",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_event/{distribution_event}",
 *     "add-form" = "/admin/distribution/distribution_event/add",
 *     "edit-form" = "/admin/distribution/distribution_event/{distribution_event}/edit",
 *     "delete-form" = "/admin/distribution/distribution_event/{distribution_event}/delete",
 *     "collection" = "/admin/distribution/distribution_event",
 *   },
 *   field_ui_base_route = "distribution_event.settings"
 * )
 */
class Event extends ContentEntityBase implements EventInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
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
  public function isValid() {
    return (bool)$this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setValid($valid) {
    $this->set('status', $valid ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    return $this->get('order_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    if (!$this->get('amount')->isEmpty()) {
      return $this->get('amount')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAmountPromotion() {
    if (!$this->get('amount_promotion')->isEmpty()) {
      return $this->get('amount_promotion')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAmountChain() {
    if (!$this->get('amount_chain')->isEmpty()) {
      return $this->get('amount_chain')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAmountLeader() {
    if (!$this->get('amount_leader')->isEmpty()) {
      return $this->get('amount_leader')->first()->toPrice();
    }
  }

  /**
   * @inheritdoc
   */
  public function getDistributor() {
    return $this->get('distributor_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('事件的订单'))
      ->setDescription(t('产生分销事件的订单。'))
      ->setSetting('target_type', 'commerce_order')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label'
      ]);

    $fields['order_item_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('事件的订单项'))
      ->setDescription(t('产生分销事件的订单项。'))
      ->setSetting('target_type', 'commerce_order_item')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label'
      ]);

    $fields['distributor_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('成交订单的所属分销商'))
      ->setSetting('target_type', 'distribution_distributor')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label'
      ]);

    $fields['target_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('标的物'))
      ->setDescription(t('订单项所关联的可购买实体，所对应的分销标的物。'))
      ->setSetting('target_type', 'distribution_target')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label'
      ]);

    $fields['amount'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('商品的成交金额'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ]);

    $fields['amount_promotion'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('商品设置的推广佣金总额'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ]);

    $fields['amount_chain'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('商品设置的链级佣金总额'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ]);

    $fields['amount_leader'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('商品设置的团队领导佣金总额'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('事件名称'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
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
