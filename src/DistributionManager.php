<?php

namespace Drupal\distribution;
use Drupal\finance\FinanceManagerInterface;

/**
 * Class DistributionManager.
 */
class DistributionManager implements DistributionManagerInterface {

  /**
   * Drupal\finance\FinanceManagerInterface definition.
   *
   * @var \Drupal\finance\FinanceManagerInterface
   */
  protected $financeFinanceManager;
  /**
   * Constructs a new DistributionManager object.
   */
  public function __construct(FinanceManagerInterface $finance_finance_manager) {
    $this->financeFinanceManager = $finance_finance_manager;
  }

}
