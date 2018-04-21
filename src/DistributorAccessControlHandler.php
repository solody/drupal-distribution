<?php

namespace Drupal\distribution;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Distributor entity.
 *
 * @see \Drupal\distribution\Entity\Distributor.
 */
class DistributorAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\distribution\Entity\DistributorInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view distributor entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit distributor entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete distributor entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add distributor entities');
  }

}
