<?php

namespace Drupal\personal_budget_tracker\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for an income source entity type.
 */
class IncomeSourceSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'income_source_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['settings'] = [
      '#markup' => $this->t('No other settings is needed.'),
    ];

//    $form['actions'] = [
//      '#type' => 'actions',
//    ];
//
//    $form['actions']['submit'] = [
//      '#type' => 'submit',
//      '#value' => $this->t('Save'),
//    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The configuration has been updated.'));
  }

}
