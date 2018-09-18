<?php

namespace Drupal\distribution\Entity;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\distribution\Event\TaskAcceptanceEvent;
use Drupal\Tests\Core\Datetime\DateTest;
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
class Acceptance extends ContentEntityBase implements AcceptanceInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    // 检查成绩是否达到任务完成标准，设置完成状态
    if (!$this->isCompleted()) {
      if ($this->canCompleted()) {
        $this->setCompleted(true);
        // 分发任务完成事件
        $this->getEventDispatcher()
          ->dispatch(TaskAcceptanceEvent::ACCEPTANCE_COMPLETE, new TaskAcceptanceEvent($this));
      }
    }
  }

  /**
   * @return \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  private function getEventDispatcher() {
    return \Drupal::getContainer()->get('event_dispatcher');
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
  public function isCompleted() {
    return (bool)$this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setCompleted($completed) {
    $this->set('status', $completed ? TRUE : FALSE);
    return $this;
  }

  /**
   * @return int
   */
  public function getTaskId() {
    return $this->get('task_id')->target_id;
  }

  /**
   * @return TaskInterface
   */
  public function getTask() {
    return $this->get('task_id')->entity;
  }

  /**
   * @param TaskInterface $task
   * @return $this
   */
  public function setTask(TaskInterface $task) {
    $this->set('task_id', $task);
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getDistributor()
  {
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

  /**
   * @inheritdoc
   */
  public function getAchievement() {
    return (float)$this->get('achievement')->value;
  }

  /**
   * @inheritdoc
   */
  public function addAchievement(AchievementInterface $achievement) {
    $this->set('achievement', $this->getAchievement() + $achievement->getScore());
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function subtractAchievement(AchievementInterface $achievement) {
    $score = $this->getAchievement() - $achievement->getScore();
    if ($score < 0) $score = 0;
    $this->set('achievement', $score);
    return $this;
  }

  /**
   * 计算一个订单在一个任务中可获得的分数
   * @param OrderInterface $commerce_order
   * @return float
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function computeScore(OrderInterface $commerce_order) {
    // 如果订单的时间已经超出了任务完成周期，那么直接返回0分
    $complete_limit_datetime = new \DateTime('now',  new \DateTimeZone(\Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface::STORAGE_TIMEZONE));
    $complete_limit_datetime->setTimestamp($this->getCreatedTime());
    $complete_limit_datetime->add(new \DateInterval('P' . $this->getTask()->getCycle() . 'D'));

    if ($commerce_order->getPlacedTime() > $complete_limit_datetime->getTimestamp()) return 0;
    else return $this->getTask()->getBundlePlugin()->computeScore($this, $commerce_order);
  }

  /**
   * 检查给定分数有否完成一个任务
   * @param $score
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function canCompleted() {
    return $this->getTask()->getBundlePlugin()->canCompleted($this->getTask(), $this->getAchievement());
  }
}
