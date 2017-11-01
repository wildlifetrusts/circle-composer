<?php

namespace Drupal\wildlife_view_headers\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the View header entity.
 *
 * @ingroup wildlife_view_headers
 *
 * @ContentEntityType(
 *   id = "view_header",
 *   label = @Translation("View header"),
 *   handlers = {
 *     "storage" = "Drupal\wildlife_view_headers\ViewHeaderStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wildlife_view_headers\ViewHeaderListBuilder",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\wildlife_view_headers\Form\ViewHeaderForm",
 *       "add" = "Drupal\wildlife_view_headers\Form\ViewHeaderForm",
 *       "edit" = "Drupal\wildlife_view_headers\Form\ViewHeaderForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\wildlife_view_headers\ViewHeaderAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\wildlife_view_headers\ViewHeaderHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "view_header",
 *   data_table = "view_header_field_data",
 *   revision_table = "view_header_revision",
 *   revision_data_table = "view_header_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer view header entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
*      "canonical" = "/admin/structure/view_header/{view_header}",
 *     "add-form" = "/admin/structure/view_header/add",
 *     "edit-form" = "/admin/structure/view_header/{view_header}/edit",
 *     "delete-form" = "/admin/structure/view_header/{view_header}/delete",
 *     "version-history" = "/admin/structure/view_header/{view_header}/revisions",
 *     "revision" = "/admin/structure/view_header/{view_header}/revisions/{view_header_revision}/view",
 *     "revision_revert" = "/admin/structure/view_header/{view_header}/revisions/{view_header_revision}/revert",
 *     "translation_revert" = "/admin/structure/view_header/{view_header}/revisions/{view_header_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/view_header/{view_header}/revisions/{view_header_revision}/delete",
 *     "collection" = "/admin/structure/view_header",
 *   },
 *   field_ui_base_route = "view_header.settings"
 * )
 */
class ViewHeader extends RevisionableContentEntityBase implements ViewHeaderInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the view_header owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * Get the view id associated with a View Header entity.
   *
   * @return string
   */
  public function getAssociatedViewId() {
    return $this->get('view_id')->getString();
  }

  /**
   * Get the view display id associated with a View Header entity.
   *
   * @return string
   */
  public function getAssociatedViewDisplayId() {
    return $this->get('view_display_id')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the View header entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the View header entity.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue('');

    $fields['field_view_header_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title to be displayed on the view page.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
        'link_to_entity' => FALSE
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
        'settings' => [
          'size' => 60,
          'placeholder' => ''
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $header_type_allowed_values = [
      'none' => 'None',
      'colour' => 'Colour',
      'image' => 'Image',
      'silhouette' => 'Silhouette',
    ];

    $fields['field_header_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Header type'))
      ->setDescription(t('The type of header.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setSetting('allowed_values', $header_type_allowed_values)
      ->setDefaultValue('none')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 1,
        'settings' => [],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['view_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('View page ID'))
      ->setDescription(t('The view page ID the header belongs to.'))
      ->setReadOnly(TRUE);

    $fields['view_display_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('View page ID'))
      ->setDescription(t('The view page ID the header belongs to.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the View header is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
