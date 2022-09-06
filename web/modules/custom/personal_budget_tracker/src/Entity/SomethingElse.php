<?php

namespace Drupal\personal_budget_tracker\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\personal_budget_tracker\SomethingElseInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the something else entity class.
 *
 * @ContentEntityType(
 *   id = "something_else",
 *   label = @Translation("Something Else"),
 *   label_collection = @Translation("Something Elses"),
 *   label_singular = @Translation("something else"),
 *   label_plural = @Translation("something elses"),
 *   label_count = @PluralTranslation(
 *     singular = "@count something elses",
 *     plural = "@count something elses",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\personal_budget_tracker\SomethingElseListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\personal_budget_tracker\Form\SomethingElseForm",
 *       "edit" = "Drupal\personal_budget_tracker\Form\SomethingElseForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\personal_budget_tracker\Routing\SomethingElseHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "something_else",
 *   revision_table = "something_else_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer something else",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/something-else",
 *     "add-form" = "/something-else/add",
 *     "canonical" = "/something-else/{something_else}",
 *     "edit-form" = "/something-else/{something_else}",
 *     "delete-form" = "/something-else/{something_else}/delete",
 *   },
 *   field_ui_base_route = "entity.something_else.settings",
 * )
 */
class SomethingElse extends RevisionableContentEntityBase implements SomethingElseInterface {

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
      ->setDescription(t('The time that the something else was created.'))
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
      ->setDescription(t('The time that the something else was last edited.'));

    return $fields;
  }

}
