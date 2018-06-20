<?php
namespace Drupal\distribution\Event;

use Drupal\distribution\Entity\AcceptanceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the TaskEvent.
 */
class TaskAcceptanceEvent extends Event
{
    const ACCEPTANCE_COMPLETE = 'distribution.task.acceptance.complete';

    /**
     * @var AcceptanceInterface
     */
    protected $acceptance;

    /**
     * TaskEvent constructor.
     * @param AcceptanceInterface $acceptance
     */
    public function __construct(AcceptanceInterface $acceptance)
    {
        $this->acceptance = $acceptance;
    }

    public function getAcceptance()
    {
        return $this->acceptance;
    }
}
