<?php

namespace Drupal\distribution\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'distribution.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'distribution_settings_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('distribution.settings');

        $data = $config->getRawData();

        $form['commission'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('佣金设置'),
        ];

        $form['commission']['promotion'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('启用推广佣金'),
            '#default_value' => $config->get('commission.promotion'),
        ];
        $form['commission']['chain'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('启用链级佣金'),
            '#default_value' => $config->get('commission.chain'),
        ];
        $form['commission']['leader'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('启用团队领导佣金'),
            '#default_value' => $config->get('commission.leader'),
        ];

        $form['transform'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('用户转化设置'),
            '#description' => $this->t('设置普通用户转化如何转化为分销用户'),
        ];

        $form['transform']['auto'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('自动转化'),
            '#description' => $this->t('是否开启购买商品后自动转化'),
            '#default_value' => $config->get('transform.auto'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);

        $this->config('distribution.settings')
            ->set('commission.promotion', $form_state->getValue('promotion'))
            ->set('commission.chain', $form_state->getValue('chain'))
            ->set('commission.leader', $form_state->getValue('leader'))
            ->set('transform.auto', $form_state->getValue('auto'))
            ->save();
    }

}
