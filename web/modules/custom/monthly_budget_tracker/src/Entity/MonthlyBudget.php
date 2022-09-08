<?php

namespace Drupal\monthly_budget_tracker\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\monthly_budget_tracker\MonthlyBudgetInterface;
use Drupal\user\EntityOwnerTrait;
use Illuminate\Support\Arr;

/**
 * Defines the monthly budget entity class.
 *
 * @ContentEntityType(
 *   id = "monthly_budget",
 *   label = @Translation("Monthly Budget"),
 *   label_collection = @Translation("Monthly Budgets"),
 *   label_singular = @Translation("monthly budget"),
 *   label_plural = @Translation("monthly budgets"),
 *   label_count = @PluralTranslation(
 *     singular = "@count monthly budgets",
 *     plural = "@count monthly budgets",
 *   ),
 *   handlers = {
 *     "list_builder" =
 *     "Drupal\monthly_budget_tracker\MonthlyBudgetListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\monthly_budget_tracker\MonthlyBudgetAccess",
 *     "form" = {
 *       "add" = "Drupal\monthly_budget_tracker\Form\MonthlyBudgetForm",
 *       "step_1" =
 *       "Drupal\monthly_budget_tracker\Form\MonthlyBudgetFormStep1",
 *       "step_2" =
 *       "Drupal\monthly_budget_tracker\Form\MonthlyBudgetFormStep2",
 *       "step_3" =
 *       "Drupal\monthly_budget_tracker\Form\MonthlyBudgetFormStep3",
 *       "edit" = "Drupal\monthly_budget_tracker\Form\MonthlyBudgetForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "monthly_budget",
 *   admin_permission = "administer monthly budget",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/monthly-budget",
 *     "add-form" = "/monthly-budget/add",
 *     "canonical" = "/monthly-budget/{monthly_budget}",
 *     "edit-form" = "/monthly-budget/{monthly_budget}/edit",
 *     "delete-form" = "/monthly-budget/{monthly_budget}/delete",
 *   },
 *   field_ui_base_route = "entity.monthly_budget.settings",
 * )
 */
class MonthlyBudget extends ContentEntityBase
  implements MonthlyBudgetInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  public function getOwner() {
    return $this->get('uid')->entity;
  }

  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type'     => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size'           => 60,
          'placeholder'    => '',
        ],
        'weight'   => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the monthly budget was created.'))
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type'   => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the monthly budget was last edited.'));

    return $fields;
  }

  public function getIncomeSourceSummary() {
    $incomeSources
      = $this->get('field_monthly_income_sources')->referencedEntities();
    $summary = [];
    foreach ($incomeSources as $index => $incomeSource) {
      $source
        = $incomeSource->get('field_income_source')->entity->get('name')->value;
      $amount = $incomeSource->get('field_net_amount')->value;
      $summary[$index]['source'] = "$source";
      $summary[$index]['amount'] = "$" . number_format($amount, 2);
    }
    return $summary;
  }

  public function getExpenseSummary() {
    $monthlyExpenses
      = $this->get('field_monthly_expenses')->referencedEntities();
    $summary = [];
    foreach ($monthlyExpenses as $index => $monthlyExpense) {
      $source
        = $monthlyExpense->get('field_expense_type')->entity->get('name')->value;
      $amount = $monthlyExpense->get('field_expense_amount')->value;
      $summary[$index]['source'] = "$source";
      $summary[$index]['amount'] = "$" . number_format($amount, 2);
    }
    return $summary;
  }

  public function getTotalIncome($formatted = FALSE) {
    $total = 0;
    $incomeSources
      = $this->get('field_monthly_income_sources')->referencedEntities();
    foreach ($incomeSources as $incomeSource) {
      $total += $incomeSource->get('field_net_amount')->value;
    }
    return $formatted ? number_format($total, 2) : $total;
  }

  public function getTotalExpenses($formatted = FALSE) {
    $total = 0;
    $expenses = $this->get('field_monthly_expenses')->referencedEntities();
    foreach ($expenses as $expense) {
      $total += $expense->get('field_expense_amount')->value;
    }
    return $formatted ? number_format($total, 2) : $total;
  }

  public function getPercentageOfIncomeSpent($formatted = FALSE) {
    $percentage = ($this->getTotalExpenses() / $this->getTotalIncome()) * 100;
    return $formatted ? number_format($percentage, 0) : $percentage;
  }

  public function getCashBalance($formatted = FALSE) {
    $balance = $this->getTotalIncome() - $this->getTotalExpenses();
    return $formatted ? number_format($balance, 2) : $balance;
  }

  public function getIncomeDetails() {
    $details['detail'] = [
      '#type'    => 'table',
      '#caption' => t('Monthly Income'),
      '#header'  => [
        t('Source'),
        t('Amount'),
      ],
    ];
    $incomeSources
      = $this->get('field_monthly_income_sources')->referencedEntities();
    for ($i = 0; $i < count($incomeSources); $i++) {
      $details['detail'][$i]['source'] = [
        '#type'   => 'markup',
        '#markup' => $incomeSources[$i]->get('field_income_source')->entity->get('name')->value,
      ];
      $details['detail'][$i]['amount'] = [
        '#type'   => 'markup',
        '#markup' => $incomeSources[$i]->get('field_net_amount')->value,
      ];
    }
    return $details;
  }

  public function getPercentageChart() {
    $build['mychart'] = [
      '#data'       => [
        'labels'   => ['Income', 'Expense'],
        'datasets' => [
          [
            'label'                => 'Budget',
            'data'                 => [$this->getTotalIncome(), $this->getTotalExpenses()],
            'backgroundColor'      => ['#00557f', '#f8413c'],
            'hoverBackgroundColor' => ['#004060', '#9b2926'],
          ],
        ],
      ],
      '#graph_type' => 'halfdonut',
      '#id'         => 'income_percentage',
      '#options'    => [
        'title' => [
          'text' => t($this->getPercentageOfIncomeSpent(TRUE) . "%"),
        ],
      ],
      '#plugins'    => ['halfdonutTotal'],
      '#type'       => 'chartjs_api',
    ];
    return $build;
  }

  public function getChart() {
    $build['mychart'] = [
      '#data'       => [
        'labels'   => ['Income', 'Expenses'],
        'datasets' => [
          [
            'label'                => 'Budget',
            'data'                 => [
              $this->getTotalIncome(),
              $this->getTotalExpenses(),
            ],
            'backgroundColor'      => ['#38bdf8', '#fb7185'],
            'hoverBackgroundColor' => ['#0284c7', '#e11d48'],
          ],
        ],
      ],
      '#graph_type' => 'bar',
      '#id'         => 'income_vs_expenses',
      '#type'       => 'chartjs_api',
    ];
    return $build;
  }

  public function getExpenseDetails() {
    $details['detail'] = [
      '#type'    => 'table',
      '#caption' => t('Monthly Expenses'),
      '#header'  => [
        t('Source'),
        t('Amount'),
      ],
    ];
    $monthlyExpenses
      = $this->get('field_monthly_expenses')->referencedEntities();
    for ($i = 0; $i < count($monthlyExpenses); $i++) {
      $details['detail'][$i]['source'] = [
        '#type'   => 'markup',
        '#markup' => $monthlyExpenses[$i]->get('field_expense_type')->entity->get('name')->value,
      ];
      $details['detail'][$i]['amount'] = [
        '#type'   => 'markup',
        '#markup' => $monthlyExpenses[$i]->get('field_expense_amount')->value,
      ];
    }
    return $details;
  }

}
