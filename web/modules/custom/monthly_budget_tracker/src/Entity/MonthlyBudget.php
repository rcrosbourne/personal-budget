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
 *     "list_builder" = "Drupal\monthly_budget_tracker\MonthlyBudgetListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\monthly_budget_tracker\Form\MonthlyBudgetForm",
 *       "step_1" = "Drupal\monthly_budget_tracker\Form\MonthlyBudgetFormStep1",
 *       "step_2" = "Drupal\monthly_budget_tracker\Form\MonthlyBudgetFormStep2",
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
class MonthlyBudget extends ContentEntityBase implements MonthlyBudgetInterface {

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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the monthly budget was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the monthly budget was last edited.'));

    return $fields;
  }

  public function getIncomeSourceSummary() {
      $incomeSources = $this->get('field_monthly_income_sources')->referencedEntities();
      $summary = [];
      foreach ($incomeSources as $index => $incomeSource) {
        $source = $incomeSource->get('field_income_source')->entity->get('name')->value;
        $amount = $incomeSource->get('field_net_amount')->value;
        $summary[$index]['source'] = "$source";
        $summary[$index]['amount'] =  "$". number_format($amount, 2);
      }
      return $summary;
  }

}
