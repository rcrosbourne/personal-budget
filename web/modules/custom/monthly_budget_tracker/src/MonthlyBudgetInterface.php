<?php

namespace Drupal\monthly_budget_tracker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a monthly budget entity type.
 */
interface MonthlyBudgetInterface
  extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Returns income sources as a table row for the table component template
   *
   * @return array
   */
  public function getIncomeSourcesAsTableRow(): array;

  /**
   * Returns expenses as a table row for the table component template
   *
   * @return array
   */
  public function getExpensesAsTableRow(): array;

  /**
   * This returns an income source summary array which includes
   * - Name of the income source.
   * - Amount of the income source
   * This function is used by the MonthlyBudgetForm to display a more readable
   * summary when the accordion is in the collapsed state.
   *
   * @return array
   */
  public function getIncomeSourceSummary(): array;

  /**
   * This returns an expense summary array which includes
   * - Name of the expense.
   * - Amount of the expense.
   * This function is used by the MonthlyBudgetForm to display a more readable
   * summary when the accordion is in the collapsed state.
   *
   * @return array
   */
  public function getExpenseSummary(): array;

  /**
   * This returns the total monthly income as a formatted string ($formatted =
   * TRUE) or as float (default).
   *
   * @param  bool  $formatted
   *
   * @return string|float
   */
  public function getTotalIncome(bool $formatted = FALSE): string|float;

  /**
   * This returns the total monthly income as a formatted string ($formatted =
   * TRUE) or as float (default).
   *
   * @param  bool  $formatted
   *
   * @return string|float
   */
  public function getTotalExpenses(bool $formatted = FALSE): string|float;

  /**
   * This returns the percentage of income spent on expenses as a formatted
   * string ($formatted = TRUE) or as float (default).
   *
   * @param  bool  $formatted
   *
   * @return string|float
   */
  public function getPercentageOfIncomeSpent(bool $formatted = FALSE): string|float;

  /**
   * This returns the cash balance (total income - total expenses) as formatted
   * string ($formatted = TRUE) or as float (default).
   *
   * @param  bool  $formatted
   *
   * @return string|float
   */
  public function getCashBalance(bool $formatted = FALSE): string|float;

  /**
   * Returns a render array responsible for generating the half-donut chart for
   * Percentage of income spent.
   *
   * @return array
   */
  public function getPercentageChart(): array;

  /**
   * Returns a render array responsible for generating the bar chart for Income
   * vs Expenses.
   *
   * @return array
   */
  public function getIncomeVsExpensesChart(): array;

}
