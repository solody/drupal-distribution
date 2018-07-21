<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Monthly reward condition entity.
 *
 * @ConfigEntityType(
 *   id = "distribution_mr_condition",
 *   label = @Translation("Monthly reward condition"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\MonthlyRewardConditionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\distribution\Form\MonthlyRewardConditionForm",
 *       "edit" = "Drupal\distribution\Form\MonthlyRewardConditionForm",
 *       "delete" = "Drupal\distribution\Form\MonthlyRewardConditionDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\MonthlyRewardConditionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "distribution_mr_condition",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_mr_condition/{distribution_mr_condition}",
 *     "add-form" = "/admin/distribution/distribution_mr_condition/add",
 *     "edit-form" = "/admin/distribution/distribution_mr_condition/{distribution_mr_condition}/edit",
 *     "delete-form" = "/admin/distribution/distribution_mr_condition/{distribution_mr_condition}/delete",
 *     "collection" = "/admin/distribution/distribution_mr_condition"
 *   }
 * )
 */
class MonthlyRewardCondition extends ConfigEntityBase implements MonthlyRewardConditionInterface {

  /**
   * The Monthly reward condition ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Monthly reward condition label.
   *
   * @var string
   */
  protected $label;


  /**
   * The plugin ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    $plugin_manager = \Drupal::service('plugin.manager.commerce_condition');
    return$plugin_manager->createInstance($this->plugin, $this->getPluginConfiguration());
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginId($plugin_id) {
    $this->plugin = $plugin_id;
    $this->configuration = [];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginConfiguration(array $configuration) {
    $this->configuration = $configuration;
    return $this;
  }
}
