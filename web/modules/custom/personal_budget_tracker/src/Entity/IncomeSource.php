<?php

namespace Drupal\personal_budget_tracker\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\personal_budget_tracker\IncomeSourceInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the income source entity class.
 *
 * @ContentEntityType(
 *   id = "income_source",
 *   label = @Translation("Income Source"),
 *   label_collection = @Translation("Income Sources"),
 *   label_singular = @Translation("income source"),
 *   label_plural = @Translation("income sources"),
 *   label_count = @PluralTranslation(
 *     singular = "@count income sources",
 *     plural = "@count income sources",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\personal_budget_tracker\IncomeSourceListBuilder",
 *     "views_data" = "Drupal\personal_budget_tracker\IncomeSourceViewsData",
 *     "access" = "Drupal\personal_budget_tracker\IncomeSourceAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\personal_budget_tracker\Form\IncomeSourceForm",
 *       "edit" = "Drupal\personal_budget_tracker\Form\IncomeSourceForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "income_source",
 *   revision_table = "income_source_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer income source",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/income-source",
 *     "add-form" = "/income-source/add",
 *     "canonical" = "/income-source/{income_source}",
 *     "edit-form" = "/income-source/{income_source}/edit",
 *     "delete-form" = "/income-source/{income_source}/delete",
 *   },
 *   field_ui_base_route = "entity.income_source.settings",
 * )
 */
class IncomeSource extends RevisionableContentEntityBase implements IncomeSourceInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    $values['uid'] = \Drupal::currentUser()->id();
  }

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
      ->setRevisionable(TRUE)
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

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
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
      ->setRevisionable(TRUE)
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
      ->setDescription(t('The time that the income source was created.'))
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
      ->setDescription(t('The time that the income source was last edited.'));

    return $fields;
  }

}
