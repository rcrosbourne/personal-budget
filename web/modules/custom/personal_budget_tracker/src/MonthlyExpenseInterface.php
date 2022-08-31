<?php

namespace Drupal\personal_budget_tracker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a monthly expense entity type.
 */
interface MonthlyExpenseInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  public function getExpenseName();
}
