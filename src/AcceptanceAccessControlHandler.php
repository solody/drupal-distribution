<?php

namespace Drupal\distribution;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Acceptance entity.
 *
 * @see \Drupal\distribution\Entity\Acceptance.
 */
class AcceptanceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\distribution\Entity\AcceptanceInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished acceptance entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published acceptance entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit acceptance entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete acceptance entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add acceptance entities');
  }

}
