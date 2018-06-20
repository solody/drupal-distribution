<?php
namespace Drupal\distribution\Event;

use Drupal\distribution\Entity\Commission;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the commission event.
 */
class CommissionEvent extends Event
{
    const PROMOTION = 'distribution.commission.promotion';
    const CHAIN = 'distribution.commission.chain';
    const LEADER = 'distribution.commission.leader';
    const TASK = 'distribution.commission.task';

    /**
     * @var Commission
     */
    protected $commission;

    /**
     * CommissionEvent constructor.
     * @param Commission $commission
     */
    public function __construct(Commission $commission)
    {
        $this->commission = $commission;
    }

    public function getCommission()
    {
        return $this->commission;
    }
}
