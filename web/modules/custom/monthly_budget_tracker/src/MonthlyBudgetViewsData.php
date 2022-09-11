<?php

namespace Drupal\monthly_budget_tracker;

use Drupal\views\EntityViewsData;

class MonthlyBudgetViewsData extends EntityViewsData {

  public function getViewsData() {
    $viewsData = parent::getViewsData();
    $viewsData['monthly_budget']['monthly_budget_tracker_total_monthly_income'] = [
      'title' => $this->t("Total Monthly Income"),
      'field' => [
        'help' => $this->t('Shows the total monthly income for a given monthly budget'),
        'id'   => 'monthly_budget_tracker_total_monthly_income',
      ],
    ];
    $viewsData['monthly_budget']['monthly_budget_tracker_total_monthly_expenses'] = [
      'title' => $this->t("Total Monthly Expenses"),
      'field' => [
        'help' => $this->t('Shows the total expense for a given monthly budget'),
        'id'   => 'monthly_budget_tracker_total_monthly_expenses',
      ],
    ];
    return $viewsData;
  }

}