<?php

namespace Drupal\monthly_budget_tracker\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the monthly budget entity edit forms.
 */
class MonthlyBudgetFormStep3 extends ContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][]
      = 'monthly_budget_tracker/monthly_budget_tracker';
    //update the field list
    $form['actions']['submit']['#value'] = $this->t('View Breakdown');
    $form['actions']['cancel'] = [
      '#type'   => 'submit',
      '#value'  => $this->t("Cancel"),
      '#submit' => ['::cancel'],
    ];
    $form['actions']['step2'] = [
      '#type'   => 'submit',
      '#value'  => $this->t("Back"),
      '#submit' => ['::goBack'],
    ];
    $expenses = $this->entity->getExpenseSummary();
    for ($i = 0; $i < count($expenses); $i++) {
      $form['field_monthly_expenses']['widget'][$i]['top']['type']['label']['#markup']
        = $expenses[$i]['source'];
      $form['field_monthly_expenses']['widget'][$i]['top']['summary']['fields_info']['#summary']['content'][0]
        = $expenses[$i]['amount'];
    }
    return $form;
  }

  public function cancel(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.monthly_budget.collection');
  }

  public function goBack(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.monthly_budget.add_budget_step2', ['monthly_budget' => $this->entity->id()]);
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

    $form_state->setRedirect('entity.monthly_budget.canonical', ['monthly_budget' => $this->entity->id()]);

    return $result;
  }

}
