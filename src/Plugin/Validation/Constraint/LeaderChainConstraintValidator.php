<?php

namespace Drupal\distribution\Plugin\Validation\Constraint;

use Drupal\distribution\Entity\Distributor;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\distribution\Entity\LeaderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * LeaderChainConstraintValidator
 */
class LeaderChainConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!count($items)) return;
    $leader = $items->getParent()->getValue();
    if ($leader instanceof LeaderInterface) {
      // 检查关系链，只允许2级经销商，限制人数
      $distributor = $leader->getDistributor();
      if ($leader->get('state')->value === 'approved' && $leader->get('status')->value) {
        // 先检查下方有没有经销商，如果有直接错误
        if ($this->countDownstreamLeaders($distributor) > 0)
          $this->context->addViolation('操作失败：'.$constraint->downstreamLimitation);
        // 再检查上方有没有经销商，如果达到2个，给出错误
        if ($this->countUpstreamLeaders($distributor) > 1)
          $this->context->addViolation('操作失败：'.$constraint->upstreamLimitation);
      }
    }
  }

  /**
   * 检查一个分销商的所有分支上线中，存在有效的团队领导节点的数量
   * @param DistributorInterface $distributor
   * @return int
   */
  private function countUpstreamLeaders(DistributorInterface $distributor) {
    $total = 0;
    $current_distributor = $distributor->getUpstreamDistributor();
    while($current_distributor instanceof DistributorInterface) {
      if ($current_distributor->isLeader()) $total++;
      $current_distributor = $current_distributor->getUpstreamDistributor();
    }
    return $total;
  }

  /**
   * 检查一个分销商的所有分支下线中，存在有效的团队领导节点的数量
   * @param DistributorInterface $distributor
   * @return integer
   */
  private function countDownstreamLeaders(DistributorInterface $distributor) {
    // 查找所设定级数内的所有下游
    $distributors = Distributor::loadMultiple();

    $getDownstream = function ($upstream_distributor = null) use ($distributors) {
      $rs = [];
      foreach ($distributors as $distributor) {
        /** @var Distributor $distributor */
        if ($upstream_distributor instanceof Distributor) {
          if ($distributor->getUpstreamDistributor() instanceof Distributor && $distributor->getUpstreamDistributor()->id() === $upstream_distributor->id()) {
            $rs[] = $distributor;
          }
        } else {
          if (empty($distributor->getUpstreamDistributor())) {
            $rs[] = $distributor;
          }
        }
      }
      return $rs;
    };

    $current_upstream_distributor = $distributor;
    $current_distributors = $getDownstream($current_upstream_distributor);
    $total = 0;

    while (count($current_distributors)) {
      $new_current_distributors = [];
      foreach ($current_distributors as $current_distributor) {
        /** @var DistributorInterface $current_distributor */
        if ($current_distributor->isLeader()) $total++;
        $new_current_distributors = array_merge($new_current_distributors, $getDownstream($current_distributor));
      }

      $current_distributors = $new_current_distributors;
    };

    return $total;
  }
}