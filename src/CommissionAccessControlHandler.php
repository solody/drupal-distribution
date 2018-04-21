<?php

namespace Drupal\distribution;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Commission entity.
 *
 * @see \Drupal\distribution\Entity\Commission.
 */
class CommissionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\distribution\Entity\CommissionInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished commission entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published commission entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit commission entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete commission entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add commission entities');
  }

}
