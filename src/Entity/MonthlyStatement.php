<?php

namespace Drupal\distribution\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Monthly statement entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_monthly_statement",
 *   label = @Translation("Monthly statement"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\MonthlyStatementListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\MonthlyStatementViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\MonthlyStatementForm",
 *       "add" = "Drupal\distribution\Form\MonthlyStatementForm",
 *       "edit" = "Drupal\distribution\Form\MonthlyStatementForm",
 *       "delete" = "Drupal\distribution\Form\MonthlyStatementDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\MonthlyStatementAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\MonthlyStatementHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_monthly_statement",
 *   admin_permission = "administer monthly statement entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode"
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_monthly_statement/{distribution_monthly_statement}",
 *     "add-form" = "/admin/distribution/distribution_monthly_statement/add",
 *     "edit-form" = "/admin/distribution/distribution_monthly_statement/{distribution_monthly_statement}/edit",
 *     "delete-form" = "/admin/distribution/distribution_monthly_statement/{distribution_monthly_statement}/delete",
 *     "collection" = "/admin/distribution/distribution_monthly_statement",
 *   },
 *   field_ui_base_route = "distribution_monthly_statement.settings"
 * )
 */
class MonthlyStatement extends ContentEntityBase implements MonthlyStatementInterface {

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
  public function setMonth($month) {
    $this->set('month', $month);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMonth() {
    return $this->get('month')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRewardTotal() {
    if (!$this->get('reward_total')->isEmpty()) {
      return $this->get('reward_total')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setRewardTotal(Price $price) {
    $this->set('reward_total', $price);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getRewardAssigned() {
    if (!$this->get('reward_assigned')->isEmpty()) {
      return $this->get('reward_assigned')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setRewardAssigned(Price $price) {
    $this->set('reward_assigned', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuantityAssigned() {
    return $this->get('quantity_assigned')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuantityAssigned($quantity) {
    $this->set('quantity_assigned', $quantity);
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Monthly statement entity.'))
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

    $fields['month'] = BaseFieldDefinition::create('string')
      ->setLabel(t('月份'))
      ->setDescription(t('The month of the Monthly statement entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string'
      ])
      ->setRequired(TRUE);

    $fields['reward_total'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('本月度奖金总额'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ]);

    $fields['reward_assigned'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('本月度已分配的奖金总额'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'commerce_price_default'
      ]);

    $fields['quantity_assigned'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('达到奖励条件的总人数'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 1)
      ->setDefaultValue(1)
      ->setDisplayOptions('view', [
        'type' => 'number_integer'
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
