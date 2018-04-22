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

        $form['enable'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('启用系统'),
            '#description' => $this->t('如果关闭此选项，系统将不会进行订单的佣金处理，也不会自动转化订单用户为分销用户。'),
            '#default_value' => $config->get('enable'),
        ];

        $form['enable_amount_off'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('开启分销优惠'),
            '#description' => $this->t('允许商品设置一个优惠金额，普通用户通过推广链接购买，或分销用户购买时，将减免此金额。'),
            '#default_value' => $config->get('enable_amount_off'),
        ];

        $form['commission'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('佣金设置'),
        ];

        $form['commission']['compute_mode'] = array(
            '#type' => 'radios',
            '#title' => $this->t('佣金计算模式'),
            '#default_value' => $config->get('commission.compute_mode'),
            '#options' => array('fixed_amount' => $this->t('固定金额'), 'dynamic_percentage' => $this->t('对成交金额按一定百分比动态计算')),
        );
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
        $form['commission']['promotion_is_part_of_chain'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('推广佣金从链级佣金中计算（动态百分比计算模式下有效）'),
            '#default_value' => $config->get('commission.promotion_is_part_of_chain'),
        ];


        $form['chain_commission'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('链级分佣设置')
        ];

        $form['chain_commission']['level_1'] = array(
            '#type' => 'number',
            '#title' => $this->t('1级'),
            '#default_value' => $config->get('chain_commission.level_1'),
            '#min' => 0.00,
            '#max' => 100.00,
            '#step' => 0.01,
            '#field_suffix' => '%'
        );
        $form['chain_commission']['level_2'] = array(
            '#type' => 'number',
            '#title' => $this->t('2级'),
            '#default_value' => $config->get('chain_commission.level_2'),
            '#min' => 0.00,
            '#max' => 100.00,
            '#step' => 0.01,
            '#field_suffix' => '%'
        );
        $form['chain_commission']['level_3'] = array(
            '#type' => 'number',
            '#title' => $this->t('3级'),
            '#default_value' => $config->get('chain_commission.level_3'),
            '#min' => 0.00,
            '#max' => 100.00,
            '#step' => 0.01,
            '#field_suffix' => '%'
        );
        
        

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
            ->set('enable', $form_state->getValue('enable'))
            ->set('enable_amount_off', $form_state->getValue('enable_amount_off'))
            ->set('commission.compute_mode', $form_state->getValue('compute_mode'))
            ->set('commission.promotion', $form_state->getValue('promotion'))
            ->set('commission.chain', $form_state->getValue('chain'))
            ->set('commission.leader', $form_state->getValue('leader'))
            ->set('commission.promotion_is_part_of_chain', $form_state->getValue('promotion_is_part_of_chain'))
            ->set('chain_commission.level_1', $form_state->getValue('level_1'))
            ->set('chain_commission.level_2', $form_state->getValue('level_2'))
            ->set('chain_commission.level_3', $form_state->getValue('level_3'))
            ->set('transform.auto', $form_state->getValue('auto'))
            ->save();
    }

}
