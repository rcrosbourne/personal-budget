<?php

namespace Drupal\monthly_budget_tracker\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the monthly budget entity edit forms.
 */
class MonthlyBudgetFormStep2 extends ContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][]
      = 'monthly_budget_tracker/monthly_budget_tracker';
    //update the field list
    $form['actions']['submit']['#value'] = $this->t('Save and Continue');
    $form['actions']['cancel'] = [
      '#type'   => 'submit',
      '#value'  => $this->t("Cancel"),
      '#submit' => ['::cancel'],
    ];
    $form['actions']['step1'] = [
      '#type'   => 'submit',
      '#value'  => $this->t("Back"),
      '#submit' => ['::goBack'],
    ];
    $incomeSourceSummary = $this->entity->getIncomeSourceSummary();
    for ($i = 0; $i < count($incomeSourceSummary); $i++) {
      $form['field_monthly_income_sources']['widget'][$i]['top']['type']['label']['#markup']
        = $incomeSourceSummary[$i]['source'];
      $form['field_monthly_income_sources']['widget'][$i]['top']['summary']['fields_info']['#summary']['content'][0]
        = $incomeSourceSummary[$i]['amount'];
    }
    //    $form['field_monthly_income_sources']['widget'][0]['top']['type']['label']['#markup'] = '';
    //    $form['field_monthly_income_sources']['widget'][0]['top']['summary']['fields_info']['#summary']['content'][0] .= ' $123123.00';
    //    $form['field_monthly_income_sources']['widget'][1]['top']['type']['label']['#markup'] = '';
    //    $form['field_monthly_income_sources']['widget'][1]['top']['summary']['fields_info']['#summary']['content'][0] .= ' $123123.00';
    ////    kint($form['field_monthly_income_sources']['widget'][0]['top']['summary']['fields_info']['#summary']['content'][0]);
    return $form;
  }

  public function cancel(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.monthly_budget.collection');
  }

  public function goBack(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.monthly_budget.add_budget_step1', ['monthly_budget' => $this->entity->id()]);
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

}
