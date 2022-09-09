<?php

namespace Drupal\monthly_budget_tracker\Plugin\views\field;

use Drupal\views\Annotation\ViewsField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides Total Monthly Income field handler.
 *
 * @ViewsField("monthly_budget_tracker_total_monthly_income")
 *
 * @DCG
 * The plugin needs to be assigned to a specific table column through
 * hook_views_data() or hook_views_data_alter().
 * For non-existent columns (i.e. computed fields) you need to override
 * self::query() method.
 */
class TotalMonthlyIncome extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\monthly_budget_tracker\Entity\MonthlyBudget $entity */
    $entity = $values->_entity;
    return "$".$entity->getTotalIncome(TRUE);
  }

  public function query() {
    // Computed field so no need to query nuttin!
  }

}
