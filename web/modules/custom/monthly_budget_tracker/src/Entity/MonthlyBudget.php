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
use Drupal\user\UserInterface;

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
 *     "views_data" = "Drupal\monthly_budget_tracker\MonthlyBudgetViewsData",
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

  /**
   * {@inheritdoc}
   */
  public function getOwner(): UserInterface {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId(): ?int {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
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

  /*
   * {@inheritdoc}
   */
  public function getIncomeSourcesAsTableRow(): array {
    $rows = [];
    $records = $this->get('field_monthly_income_sources')->referencedEntities();
    foreach ($records as $index => $record) {
      $rows[$index][]
        = $record->get('field_income_source')->entity->get('name')->value;
      $rows[$index][] = $record->get('field_net_amount')->value;
    }
    return $rows;
  }

  /*
   * {@inheritdoc}
   */
  public function getExpensesAsTableRow(): array {
    $rows = [];
    $records = $this->get('field_monthly_expenses')->referencedEntities();
    foreach ($records as $index => $record) {
      $rows[$index][]
        = $record->get('field_expense_type')->entity->get('name')->value;
      $rows[$index][] = $record->get('field_expense_amount')->value;
    }
    return $rows;
  }

  /*
   * {@inheritdoc}
   */
  public function getIncomeSourceSummary(): array {
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

  /*
   * {@inheritdoc}
   */
  public function getExpenseSummary(): array {
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

  /*
   * {@inheritdoc}
   */
  public function getTotalIncome($formatted = FALSE): string|float {
    $total = 0;
    $incomeSources
      = $this->get('field_monthly_income_sources')->referencedEntities();
    foreach ($incomeSources as $incomeSource) {
      $total += $incomeSource->get('field_net_amount')->value;
    }
    return $formatted ? number_format($total, 2) : $total;
  }

  /*
   * {@inheritdoc}
   */
  public function getTotalExpenses(bool $formatted = FALSE): string|float {
    $total = 0;
    $expenses = $this->get('field_monthly_expenses')->referencedEntities();
    foreach ($expenses as $expense) {
      $total += $expense->get('field_expense_amount')->value;
    }
    return $formatted ? number_format($total, 2) : $total;
  }

  /*
   * {@inheritdoc}
   */
  public function getPercentageOfIncomeSpent(bool $formatted = FALSE): string|float {
    $percentage = ($this->getTotalExpenses() / $this->getTotalIncome()) * 100;
    return $formatted ? number_format($percentage, 0) : $percentage;
  }

  /*
   * {@inheritdoc}
   */
  public function getCashBalance(bool $formatted = FALSE): string|float {
    $balance = $this->getTotalIncome() - $this->getTotalExpenses();
    return $formatted ? number_format($balance, 2) : $balance;
  }

  /*
   * {@inheritdoc}
   */
  public function getPercentageChart(): array {
    $build['mychart'] = [
      '#data'       => [
        'labels'   => ['Cash balance', 'Expense'],
        'datasets' => [
          [
            'label'                => 'Budget',
            'data'                 => [
              $this->getTotalIncome() - $this->getTotalExpenses(),
              $this->getTotalExpenses(),
            ],
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

  /*
   * {@inheritdoc}
   */
  public function getIncomeVsExpensesChart(): array {
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

}
