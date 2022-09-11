<?php

namespace Drupal\monthly_budget_tracker\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the monthly budget entity edit forms.
 */
class MonthlyBudgetForm extends ContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][]
      = 'monthly_budget_tracker/monthly_budget_tracker';
    /**
     * TODO: Refactor these array setters and accessors to using the Arr helpers.
     */
    $form['field_month']['widget'][0]['value']['#date_part_order'] = [
      'month',
      'year',
    ];
    // if there is no field month default to today
    if (empty($this->entity->get('field_month')->value)) {
      $form['field_month']['widget'][0]['value']['#default_value']
        = new DrupalDateTime('now');
    }
    // Update the summary display for income sources
    $form = $this->updateTheSummaryDisplayForIncomeSources($form);
    // Update the summary display for expenses
    $form = $this->updateTheSummaryDisplayForExpenses($form);

    $form['actions']['cancel'] = [
      '#type'                    => 'submit',
      '#value'                   => $this->t("Cancel"),
      '#submit'                  => ['::cancel'],
      '#limit_validation_errors' => [],
    ];
    return $form;
  }

  public function cancel(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('view.monthly_budget.page_1');
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link'   => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New monthly budget %label has been created.', $message_arguments));
        $this->logger('monthly_budget_tracker')->notice('Created new monthly budget %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The monthly budget %label has been updated.', $message_arguments));
        $this->logger('monthly_budget_tracker')->notice('Updated monthly budget %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.monthly_budget.canonical', ['monthly_budget' => $entity->id()]);

    return $result;
  }

  /**
   * Update the income source accordion summary view.
   * @param  array  $form
   *
   * @return array
   */
  protected function updateTheSummaryDisplayForIncomeSources(array &$form): array {
    $incomeSourceSummary = $this->entity->getIncomeSourceSummary();
    for ($i = 0; $i < count($incomeSourceSummary); $i++) {
      $form['field_monthly_income_sources']['widget'][$i]['top']['type']['label']['#markup']
        = $incomeSourceSummary[$i]['source'];
      $form['field_monthly_income_sources']['widget'][$i]['top']['summary']['fields_info']['#summary']['content'][0]
        = $incomeSourceSummary[$i]['amount'];
    }
    return $form;
  }

  /**
   * Update the expense accordion summary view.
   *
   * @param  array  $form
   *
   * @return array
   */
  protected function updateTheSummaryDisplayForExpenses(array &$form): array {
    $expenses = $this->entity->getExpenseSummary();
    for ($i = 0; $i < count($expenses); $i++) {
      $form['field_monthly_expenses']['widget'][$i]['top']['type']['label']['#markup']
        = $expenses[$i]['source'];
      $form['field_monthly_expenses']['widget'][$i]['top']['summary']['fields_info']['#summary']['content'][0]
        = $expenses[$i]['amount'];
    }
    return $form;
  }

}
