<?php

namespace Drupal\personal_budget_tracker\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the monthly expense entity edit forms.
 */
class MonthlyExpenseForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New monthly expense %label has been created.', $message_arguments));
        $this->logger('personal_budget_tracker')->notice('Created new monthly expense %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The monthly expense %label has been updated.', $message_arguments));
        $this->logger('personal_budget_tracker')->notice('Updated monthly expense %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.monthly_expense.canonical', ['monthly_expense' => $entity->id()]);

    return $result;
  }

}
