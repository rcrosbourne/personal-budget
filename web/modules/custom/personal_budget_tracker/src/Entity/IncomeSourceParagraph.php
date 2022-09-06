<?php

namespace Drupal\personal_budget_tracker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\personal_budget_tracker\IncomeSourceParagraphInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the income source paragraph entity class.
 *
 * @ContentEntityType(
 *   id = "income_source_paragraph",
 *   label = @Translation("Income Source Paragraph"),
 *   label_collection = @Translation("Income Source Paragraphs"),
 *   label_singular = @Translation("income source paragraph"),
 *   label_plural = @Translation("income source paragraphs"),
 *   label_count = @PluralTranslation(
 *     singular = "@count income source paragraphs",
 *     plural = "@count income source paragraphs",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\personal_budget_tracker\IncomeSourceParagraphListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\personal_budget_tracker\Form\IncomeSourceParagraphForm",
 *       "edit" = "Drupal\personal_budget_tracker\Form\IncomeSourceParagraphForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\personal_budget_tracker\Routing\IncomeSourceParagraphHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "income_source_paragraph",
 *   admin_permission = "administer income source paragraph",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/income-source-paragraph",
 *     "add-form" = "/income-source-paragraph/add",
 *     "canonical" = "/income-source-paragraph/{income_source_paragraph}",
 *     "edit-form" = "/income-source-paragraph/{income_source_paragraph}",
 *     "delete-form" = "/income-source-paragraph/{income_source_paragraph}/delete",
 *   },
 *   field_ui_base_route = "entity.income_source_paragraph.settings",
 * )
 */
class IncomeSourceParagraph extends Paragraph implements IncomeSourceParagraphInterface {

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
      ->setDescription(t('The time that the income source paragraph was created.'))
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
      ->setDescription(t('The time that the income source paragraph was last edited.'));

    return $fields;
  }

}
