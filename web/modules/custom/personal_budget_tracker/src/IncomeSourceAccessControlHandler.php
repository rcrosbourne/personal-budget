<?php

namespace Drupal\personal_budget_tracker;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the income source entity type.
 */
class IncomeSourceAccessControlHandler extends EntityAccessControlHandler {

  const ADMINISTER_OWN_INCOME_SOURCE = 'administer own income source';

  const ADMINISTER_INCOME_SOURCE = 'administer income source';

  const VIEW = 'view';

  const DELETE = 'delete';

  const UPDATE = 'update';

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return match ($operation) {
      self::VIEW => AccessResult::allowedIfHasPermissions(
        $account,
        [
          self::ADMINISTER_OWN_INCOME_SOURCE,
          self::ADMINISTER_INCOME_SOURCE
        ], 'OR'
      ),
      self::DELETE, self::UPDATE => AccessResult::allowedIfHasPermissions(
        $account,
        [
          self::ADMINISTER_OWN_INCOME_SOURCE,
          self::ADMINISTER_INCOME_SOURCE
        ],
        'OR',
      ),
      default => AccessResult::neutral(),
    };

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions(
      $account,
      [self::ADMINISTER_OWN_INCOME_SOURCE, self::ADMINISTER_INCOME_SOURCE],
      'OR',
    );
  }

}
