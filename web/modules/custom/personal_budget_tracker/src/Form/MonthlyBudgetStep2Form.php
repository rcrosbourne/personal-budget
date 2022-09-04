<?php

namespace Drupal\personal_budget_tracker\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the monthly budget entity edit forms.
 */
class MonthlyBudgetStep2Form extends ContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['back'] = [
      '#type'   => 'submit',
      '#value'  => $this->t('Previous'),
      '#submit' => ['::goBackToStep1'],
    ];

    $form['actions']['submit']['#value'] = $this->t('Save and Continue');
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
        parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
//    // extract elements from the form
//    $values = $form_state->getValues();
//    dpm($values['income_sources']);
//    // build up a list of Income Sources
//    // Get list of income sources
//    //Save reference to entity
  }

  public function goBackToStep1(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.add_monthly_budget.step1', ['monthly_budget' => $this->entity->id()]);
  }

  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    // Remove the delete button
    if (isset($actions['delete'])) {
      $actions['delete']['#access'] = FALSE;
    }
    $actions['cancel'] = [
      '#type'   => 'submit',
      '#value'  => $this->t('Cancel'),
      '#submit' => ['::cancelSubmit'],
    ];
    $actions['#prefix'] = 'Step 1 of 3';
    return $actions;
  }

  public function cancelSubmit(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.monthly_budget.collection');
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
        $this->logger('personal_budget_tracker')->notice('Created new monthly budget %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The monthly budget %label has been updated.', $message_arguments));
        $this->logger('personal_budget_tracker')->notice('Updated monthly budget %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.add_monthly_budget.step2', ['monthly_budget' => $entity->id()]);

    return $result;
  }

}
