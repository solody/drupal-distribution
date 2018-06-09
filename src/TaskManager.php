<?php

namespace Drupal\distribution;

use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Class TaskManager.
 */
class TaskManager implements TaskManagerInterface
{
    /**
     * Drupal\distribution\DistributionManagerInterface definition.
     *
     * @var \Drupal\distribution\DistributionManagerInterface
     */
    protected $distributionDistributionManager;

    /**
     * Constructs a new TaskManager object.
     * @param \Drupal\distribution\DistributionManagerInterface $distribution_distribution_manager
     */
    public function __construct(DistributionManagerInterface $distribution_distribution_manager)
    {
        $this->distributionDistributionManager = $distribution_distribution_manager;
    }

    /**
     * 如果订单
     * @param OrderInterface $commerce_order
     */
    public function generateTaskAchievement(OrderInterface $commerce_order)
    {
        // 查找订单的所属分销商
        // 检查该分销商是否有待完成的有效任务
        // 检查是否订单是否可追加成绩
        // 追加成绩
    }
}
