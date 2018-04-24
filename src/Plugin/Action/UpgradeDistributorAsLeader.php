<?php

namespace Drupal\distribution\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\distribution\DistributionManager;

/**
 * Publishes a product.
 *
 * @Action(
 *   id = "distribution_upgrade_distributor_as_leader",
 *   label = @Translation("Upgrade As Leader"),
 *   type = "distribution_distributor"
 * )
 */
class UpgradeDistributorAsLeader extends ActionBase
{

    /**
     * {@inheritdoc}
     */
    public function execute($entity = NULL)
    {
        /** @var \Drupal\distribution\Entity\Distributor $entity */

        /** @var DistributionManager $distribution_manager */
        $distribution_manager = \Drupal::getContainer()->get('distribution.distribution_manager');
        $distribution_manager->upgradeAsLeader($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE)
    {
        /** @var \Drupal\distribution\Entity\Distributor $object */
        $result = $object
            ->access('update', $account, TRUE)
            ->andIf($object->status->access('edit', $account, TRUE));

        return $return_as_object ? $result : $result->isAllowed();
    }

}
