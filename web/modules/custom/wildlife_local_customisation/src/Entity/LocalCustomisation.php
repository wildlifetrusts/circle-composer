<?php

namespace Drupal\wildlife_local_customisation\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;

/**
 * Defines the Local customisation entity.
 *
 * @ingroup wildlife_local_customisation
 *
 * @ContentEntityType(
 *   id = "local_customisation",
 *   label = @Translation("Local customisation"),
 *   handlers = {
 *     "storage" = "Drupal\wildlife_local_customisation\LocalCustomisationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wildlife_local_customisation\LocalCustomisationListBuilder",
 *     "views_data" = "Drupal\wildlife_local_customisation\Entity\LocalCustomisationViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\wildlife_local_customisation\Form\LocalCustomisationForm",
 *       "add" = "Drupal\wildlife_local_customisation\Form\LocalCustomisationForm",
 *       "edit" = "Drupal\wildlife_local_customisation\Form\LocalCustomisationForm",
 *       "delete" = "Drupal\wildlife_local_customisation\Form\LocalCustomisationDeleteForm",
 *     },
 *     "access" = "Drupal\wildlife_local_customisation\LocalCustomisationAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\wildlife_local_customisation\LocalCustomisationHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "local_customisation",
 *   data_table = "local_customisation_field_data",
 *   revision_table = "local_customisation_revision",
 *   revision_data_table = "local_customisation_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer local customisation entities",
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
 *     "canonical" = "/admin/structure/local_customisation/{local_customisation}",
 *     "add-form" = "/admin/structure/local_customisation/add",
 *     "edit-form" = "/admin/structure/local_customisation/{local_customisation}/edit",
 *     "delete-form" = "/admin/structure/local_customisation/{local_customisation}/delete",
 *     "version-history" = "/admin/structure/local_customisation/{local_customisation}/revisions",
 *     "revision" = "/admin/structure/local_customisation/{local_customisation}/revisions/{local_customisation_revision}/view",
 *     "revision_revert" = "/admin/structure/local_customisation/{local_customisation}/revisions/{local_customisation_revision}/revert",
 *     "translation_revert" = "/admin/structure/local_customisation/{local_customisation}/revisions/{local_customisation_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/local_customisation/{local_customisation}/revisions/{local_customisation_revision}/delete",
 *     "collection" = "/admin/structure/local_customisation",
 *   },
 *   field_ui_base_route = "local_customisation.settings"
 * )
 */
class LocalCustomisation extends RevisionableContentEntityBase implements LocalCustomisationInterface {

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

    // If no revision author has been set explicitly, make the local_customisation owner the
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
   * Get the UUID of the node associated with the local customisation.
   * @return mixed
   */
  public function getAssociatedNodeUuid() {
    return $this->get('node_uuid')->first()->getValue();
  }

  /**
   * Get the node associated with the local customisation.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getAssociatedNode() {
    $node_uuid = $this->getAssociatedNodeUuid();
    $node = \Drupal::entityManager()->loadEntityByUuid('node', $node_uuid);
    return $node;
  }

  /**
   * Returns the Local customisation blacklisted status indicator.
   *
   * Blacklisted Local customisation are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Local customisation is blacklisted.
   */
  public function isBlacklisted() {
    return (bool) $this->get('field_local_blacklist')->first()->getString();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Local customisation entity.'))
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
      ->setDescription(t('The name of the Local customisation entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['node_uuid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the node the local customisation belongs to.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Local customisation is published.'))
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
