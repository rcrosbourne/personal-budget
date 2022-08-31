<?php

namespace Drupal\personal_budget_tracker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\personal_budget_tracker\MonthlyExpenseInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the monthly expense entity class.
 *
 * @ContentEntityType(
 *   id = "monthly_expense",
 *   label = @Translation("Monthly Expense"),
 *   label_collection = @Translation("Monthly Expenses"),
 *   label_singular = @Translation("monthly expense"),
 *   label_plural = @Translation("monthly expenses"),
 *   label_count = @PluralTranslation(
 *     singular = "@count monthly expenses",
 *     plural = "@count monthly expenses",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\personal_budget_tracker\MonthlyExpenseListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\personal_budget_tracker\MonthlyIncomeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\personal_budget_tracker\Form\MonthlyExpenseForm",
 *       "edit" = "Drupal\personal_budget_tracker\Form\MonthlyExpenseForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "monthly_expense",
 *   admin_permission = "administer monthly expense",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/monthly-expense",
 *     "add-form" = "/monthly-expense/add",
 *     "canonical" = "/monthly-expense/{monthly_expense}",
 *     "edit-form" = "/monthly-expense/{monthly_expense}/edit",
 *     "delete-form" = "/monthly-expense/{monthly_expense}/delete",
 *   },
 *   field_ui_base_route = "entity.monthly_expense.settings",
 * )
 */
class MonthlyExpense extends ContentEntityBase implements MonthlyExpenseInterface {

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

//    $fields['label'] = BaseFieldDefinition::create('string')
//      ->setLabel(t('Expense'))
//      ->setDescription(t('Name of the expense / bill'))
//      ->setRequired(TRUE)
//      ->setSetting('max_length', 255)
//      ->setDisplayOptions('form', [
//        'type' => 'string_textfield',
//        'weight' => -5,
//      ])
//      ->setDisplayConfigurable('form', TRUE)
//      ->setDisplayOptions('view', [
//        'label' => 'hidden',
//        'type' => 'string',
//        'weight' => -5,
//      ])
//      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
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
      ->setDescription(t('The time that the monthly expense was created.'))
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
      ->setDescription(t('The time that the monthly expense was last edited.'));

    return $fields;
  }

  public function getExpenseName() {
    return $this->get('field_expense_type')->entity->get('name')->value;
  }

}
