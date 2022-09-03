<?php

namespace Drupal\personal_budget_tracker\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the monthly budget entity edit forms.
 */
class MonthlyBudgetForm extends ContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state) {
    return parent::buildForm($form, $form_state);
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
      'link' => $entity->toLink($this->t('View'))->toString(),
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

    $form_state->setRedirect('entity.monthly_budget.canonical', ['monthly_budget' => $entity->id()]);

    return $result;
  }

}