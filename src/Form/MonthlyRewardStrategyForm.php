<?php

namespace Drupal\distribution\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MonthlyRewardStrategyForm.
 */
class MonthlyRewardStrategyForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

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

    return $form;
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
