<?php

namespace Drupal\distribution;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Promoter entity.
 *
 * @see \Drupal\distribution\Entity\Promoter.
 */
class PromoterAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\distribution\Entity\PromoterInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished promoter entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published promoter entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit promoter entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete promoter entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add promoter entities');
  }

}
