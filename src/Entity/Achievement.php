<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Achievement entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_achievement",
 *   label = @Translation("Achievement"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\AchievementListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\AchievementViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\AchievementForm",
 *       "add" = "Drupal\distribution\Form\AchievementForm",
 *       "edit" = "Drupal\distribution\Form\AchievementForm",
 *       "delete" = "Drupal\distribution\Form\AchievementDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\AchievementAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\AchievementHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_achievement",
 *   admin_permission = "administer achievement entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_achievement/{distribution_achievement}",
 *     "add-form" = "/admin/distribution/distribution_achievement/add",
 *     "edit-form" = "/admin/distribution/distribution_achievement/{distribution_achievement}/edit",
 *     "delete-form" = "/admin/distribution/distribution_achievement/{distribution_achievement}/delete",
 *     "collection" = "/admin/distribution/distribution_achievement",
 *   },
 *   field_ui_base_route = "distribution_achievement.settings"
 * )
 */
class Achievement extends ContentEntityBase implements AchievementInterface {

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
  public function getScore() {
    return (float)$this->get('score')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setScore($score) {
    $this->set('score', $score);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['acceptance_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('已接受的任务'))
      ->setSetting('target_type', 'distribution_acceptance')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label'
      ]);

    $fields['score'] = BaseFieldDefinition::create('float')
      ->setLabel(t('本次成绩得分'))
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

    $fields['source_id'] = BaseFieldDefinition::create('dynamic_entity_reference')
      ->setLabel('成绩的来源')
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'dynamic_entity_reference_default'
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'dynamic_entity_reference_label'
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('有效'))
      ->setDefaultValue(true)
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
