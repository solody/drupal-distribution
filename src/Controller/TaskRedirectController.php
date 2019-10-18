<?php

namespace Drupal\distribution\Controller;

use Drupal\distribution\DistributionManager;
use Drupal\distribution\Entity\Distributor;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class TaskRedirectController.
 */
class TaskRedirectController extends ControllerBase
{

    /**
     * Owner.
     *
     * @param Distributor $distribution_distributor
     * @return string
     *   Return Hello string.
     */
    public function owner(Distributor $distribution_distributor)
    {
        $user = $distribution_distributor->getOwner();
        return $this->redirect('entity.user.edit_form', ['user' => $user->id()]);
    }


    public function financeAccount(Distributor $distribution_distributor)
    {
        $query = \Drupal::entityQuery('account');
        $query->condition('user_id', $distribution_distributor->getOwnerId());
        $query->condition('type', DistributionManager::FINANCE_ACCOUNT_TYPE);
        $entity_ids = $query->execute();

        if (empty($entity_ids)) return [
            '#markup' => '<h4>找不到记账账户。</h4>'
        ];

        return $this->redirect('entity.account.canonical', ['account' => array_pop($entity_ids)]);
    }
}
