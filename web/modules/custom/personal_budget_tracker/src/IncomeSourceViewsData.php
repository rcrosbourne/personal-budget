<?php

namespace Drupal\personal_budget_tracker;

use Drupal\views\EntityViewsData;

class IncomeSourceViewsData extends EntityViewsData {

  public function getViewsData() {
    $data = parent::getViewsData();
    //Add field here
    return $data;
  }
}