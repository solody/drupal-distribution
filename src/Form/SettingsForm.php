<?php

namespace Drupal\distribution\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\distribution\Entity\MonthlyRewardCondition;
use Drupal\distribution\Entity\MonthlyRewardConditionInterface;
use Drupal\distribution\Entity\MonthlyRewardStrategy;
use Drupal\distribution\Entity\MonthlyRewardStrategyInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'distribution.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'distribution_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
    $form['commission']['monthly_reward'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('启用月度奖金'),
      '#default_value' => $config->get('commission.monthly_reward'),
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
    $form['chain_commission']['distributor_self_commission'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#title' => $this->t('分销商购买订单设置'),
      '#open' => TRUE,
    ];
    $form['chain_commission']['distributor_self_commission']['enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('分销商自己获得1级佣金'),
      '#default_value' => $config->get('chain_commission.distributor_self_commission.enable'),
      '#description' => $this->t('当启用此选项时，分销商自己购买商品时，他自己将作为链级分佣中的1级佣金获得者，他的上级作为2级佣金获得者，依次类推。'),
    ];
    $form['chain_commission']['distributor_self_commission']['directly_adjust_order_amount'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('启用分销商佣金直抵'),
      '#default_value' => $config->get('chain_commission.distributor_self_commission.directly_adjust_order_amount'),
      '#description' => $this->t('当启用此选项时，分销商自己购买商品并且他自己作为链级分佣中的1级佣金获得者时，在订单中直接进行金额抵消。'),
    ];
    $form['chain_commission']['enable_senior_distributor'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('启用高级分销商'),
      '#default_value' => $config->get('chain_commission.enable_senior_distributor'),
      '#description' => $this->t('当启用此选项时，高级分销商将使用一个独立的基数来计算链级佣金，这通常使得高级会员分得更多的佣金。'),
    ];

    $form['leader_commission'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('团队领导分佣设置'),
      '#description' => $this->t('团队领导可以有二级结构，当一个领导有下级领导时，他的所有下级称为组长。')
    ];
    $form['leader_commission']['group_quantity_limit'] = array(
      '#type' => 'number',
      '#title' => $this->t('小组数量上限'),
      '#description' => $this->t('限制一个团队领导下的组长个数。'),
      '#default_value' => $config->get('leader_commission.group_quantity_limit'),
      '#min' => 0.00,
      '#max' => 100.00,
      '#step' => 1,
      '#field_suffix' => '个'
    );
    $form['leader_commission']['group_leader_percentage'] = array(
      '#type' => 'number',
      '#title' => $this->t('团队组长分佣比例'),
      '#description' => $this->t('当组长下的节点发生购买时，组长将从他的上级领导的领导佣金中分得一部分佣金。'),
      '#default_value' => $config->get('leader_commission.group_leader_percentage'),
      '#min' => 0.00,
      '#max' => 100.00,
      '#step' => 0.01,
      '#field_suffix' => '%'
    );

    $conditions = MonthlyRewardCondition::loadMultiple();
    $conditionOptions = [];
    foreach ($conditions as $condition) {
      if ($condition instanceof MonthlyRewardConditionInterface) {
        $conditionOptions[$condition->id()] = $condition->label();
      }
    }
    $strategies = MonthlyRewardStrategy::loadMultiple();
    $strategyOptions = [];
    foreach ($strategies as $strategy) {
      if ($strategy instanceof MonthlyRewardStrategyInterface) {
        $strategyOptions[$strategy->id()] = $strategy->label();
      }
    }
    $form['monthly_reward'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('月度奖金设置'),
      '#description' => $this->t('每成交一笔分销订单，从成交的产品价格中取一定比例，累计为月度奖励奖金基数。这个比例是针对每一个商品进行可以有不同设置的。当一个月度结束时，把这笔月度奖金按一定的分配策略奖励给符合条件的分销用户。')
    ];
    $form['monthly_reward']['condition'] = array(
      '#type' => 'select',
      '#title' => $this->t('奖励条件'),
      '#options' => $conditionOptions,
      '#default_value' => $config->get('monthly_reward.condition'),
    );
    $form['monthly_reward']['strategy'] = array(
      '#type' => 'select',
      '#title' => $this->t('奖励策略'),
      '#options' => $strategyOptions,
      '#default_value' => $config->get('monthly_reward.strategy'),
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('distribution.settings')
      ->set('enable', $form_state->getValue('enable'))
      ->set('enable_amount_off', $form_state->getValue('enable_amount_off'))
      ->set('commission.compute_mode', $form_state->getValue('compute_mode'))
      ->set('commission.promotion', $form_state->getValue('promotion'))
      ->set('commission.chain', $form_state->getValue('chain'))
      ->set('commission.leader', $form_state->getValue('leader'))
      ->set('commission.monthly_reward', $form_state->getValue('monthly_reward'))
      ->set('chain_commission.level_1', $form_state->getValue('level_1'))
      ->set('chain_commission.level_2', $form_state->getValue('level_2'))
      ->set('chain_commission.level_3', $form_state->getValue('level_3'))
      ->set('chain_commission.distributor_self_commission.enable', $form_state->getValue('distributor_self_commission')['enable'])
      ->set('chain_commission.distributor_self_commission.directly_adjust_order_amount', $form_state->getValue('distributor_self_commission')['directly_adjust_order_amount'])
      ->set('chain_commission.enable_senior_distributor', $form_state->getValue('enable_senior_distributor'))
      ->set('leader_commission.group_quantity_limit', $form_state->getValue('group_quantity_limit'))
      ->set('leader_commission.group_leader_percentage', $form_state->getValue('group_leader_percentage'))
      ->set('monthly_reward.condition', $form_state->getValue('condition'))
      ->set('monthly_reward.strategy', $form_state->getValue('strategy'))
      ->set('transform.auto', $form_state->getValue('auto'))
      ->save();
  }

}
