<?php

namespace Drupal\personal_budget_tracker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a monthly budget entity type.
 */
interface MonthlyBudgetInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  public function getTotalExpenses();
  public function getTotalIncome();

  public function getIncomeSources();

  public function getMonthlyExpenses();

  public function getCashBalance();
}
