<?php

namespace Drupal\distribution\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * LeaderChainConstraint
 *
 * @Constraint(
 *   id = "distribution_leader_chain_constraint",
 *   label = @Translation("Leader Chain Constraint", context = "Validation")
 * )
 */
class LeaderChainConstraint extends Constraint {
  public $downstreamLimitation = '此分销商下游关系链中已经存在有效的团队领导';
  public $upstreamLimitation = '此分销商上游关系链中已经存在2个有效的团队领导';

}