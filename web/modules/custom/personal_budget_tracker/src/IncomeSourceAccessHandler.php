<?php

namespace Drupal\personal_budget_tracker;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class IncomeSourceAccessHandler extends EntityAccessControlHandler {

  const ADMINISTER_OWN_INCOME_SOURCE = 'administer own income source';

  const VIEW = 'view';
  const UPDATE = 'update';
  const EDIT = 'edit';
  const DELETE = 'delete';

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account):
  AccessResultForbidden|AccessResultNeutral|AccessResult|AccessResultAllowed|AccessResultInterface {
    $access = AccessResult::forbidden();
    if(in_array("Administrator", $account->getRoles())) {
      return AccessResult::allowed();
    }
    if (in_array($operation, [self::VIEW, self::UPDATE, self::EDIT, self::DELETE])) {
      if ($account->hasPermission(self::ADMINISTER_OWN_INCOME_SOURCE)) {
        $access = AccessResult::allowedIf(
          $entity->getOwnerId() == $account->id()
        )->cachePerUser()->addCacheableDependency($entity);
      }
    }
    return $access;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, self::ADMINISTER_OWN_INCOME_SOURCE);
  }

}