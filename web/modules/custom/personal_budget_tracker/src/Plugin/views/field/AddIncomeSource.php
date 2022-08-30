<?php

namespace Drupal\personal_budget_tracker\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides Add Income Source field handler.
 *
 * @ViewsField("personal_budget_tracker_add_income_source")
 *
 * @DCG
 * The plugin needs to be assigned to a specific table column through
 * hook_views_data() or hook_views_data_alter().
 * For non-existent columns (i.e. computed fields) you need to override
 * self::query() method.
 */
class AddIncomeSource extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = parent::render($values);
    // @DCG Modify or replace the rendered value here.
    return $value;
  }

}
