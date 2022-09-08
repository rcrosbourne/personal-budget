<?php

namespace Drupal\monthly_budget_tracker;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class MonthlyBudgetAccess extends EntityAccessControlHandler {
  const ADMINISTER_OWN_MONTHLY_BUDGET = 'administer own monthly budget';
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if(in_array('administrator', $account->getRoles())) {
      return AccessResult::allowed();
    }

    if(!$account->hasPermission(self::ADMINISTER_OWN_MONTHLY_BUDGET)) {
      return AccessResult::forbidden("User doesn't have required permissions.");
    }

    // All access to users own budget
    if($account->id() != $entity->getOwnerId()) {
      return AccessResult::forbidden("User doesn't own this budget.");
    }

    return AccessResult::allowed();
  }

  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, self::ADMINISTER_OWN_MONTHLY_BUDGET);
  }

}