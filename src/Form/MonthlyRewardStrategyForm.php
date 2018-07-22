<?php

namespace Drupal\distribution\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\distribution\Entity\MonthlyRewardStrategyInterface;

/**
 * Class MonthlyRewardStrategyForm.
 */
class MonthlyRewardStrategyForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var MonthlyRewardStrategyInterface $distribution_mr_strategy */
    $distribution_mr_strategy = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $distribution_mr_strategy->label(),
      '#description' => $this->t("Label for the Monthly reward strategy."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $distribution_mr_strategy->id(),
      '#machine_name' => [
        'exists' => '\Drupal\distribution\Entity\MonthlyRewardStrategy::load',
      ],
      '#disabled' => !$distribution_mr_strategy->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */


    /* You will need additional form elements for your custom properties. */
    $plugins = array_column(\Drupal::service('plugin.manager.monthly_reward_strategy')->getDefinitions(), 'label', 'id');
    asort($plugins);

    // Use the first available plugin as the default value.
    if (!$distribution_mr_strategy->getPluginId()) {
      $plugin_ids = array_keys($plugins);
      $plugin = reset($plugin_ids);
      $distribution_mr_strategy->setPluginId($plugin);
    }
    // The form state will have a plugin value if #ajax was used.
    $plugin = $form_state->getValue('plugin', $distribution_mr_strategy->getPluginId());
    // Pass the plugin configuration only if the plugin hasn't been changed via #ajax.
    $plugin_configuration = $distribution_mr_strategy->getPluginId() == $plugin ? $distribution_mr_strategy->getPluginConfiguration() : [];

    $wrapper_id = Html::getUniqueId('monthly_reward_strategy-config-form');

    $form['plugin'] = [
      '#type' => 'radios',
      '#title' => $this->t('Plugin'),
      '#options' => $plugins,
      '#default_value' => $plugin,
      '#required' => TRUE,
      '#disabled' => !$this->entity->isNew(),
      '#ajax' => [
        'callback' => '::ajaxRefresh',
        'wrapper' => $wrapper_id,
      ],
    ];

    $form['configuration'] = [
      '#type' => 'commerce_plugin_configuration',
      '#plugin_type' => 'monthly_reward_strategy',
      '#plugin_id' => $plugin,
      '#default_value' => $plugin_configuration,
    ];

    return $form;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var MonthlyRewardStrategyInterface $distribution_mr_condition */
    $distribution_mr_condition = $this->entity;
    $distribution_mr_condition->setPluginConfiguration($form_state->getValue(['configuration']));
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $distribution_mr_strategy = $this->entity;
    $status = $distribution_mr_strategy->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Monthly reward strategy.', [
          '%label' => $distribution_mr_strategy->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Monthly reward strategy.', [
          '%label' => $distribution_mr_strategy->label(),
        ]));
    }
    $form_state->setRedirectUrl($distribution_mr_strategy->toUrl('collection'));
  }

}
