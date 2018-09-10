<?php
namespace Drupal\distribution\Event;

use Drupal\distribution\Entity\Commission;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the RewardTransferredEvent event.
 */
class RewardTransferredEvent extends Event
{
    const RewardTransferred = 'distribution.RewardTransferredEvent';

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
