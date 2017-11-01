<?php

namespace Drupal\wildlife_author\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Author entity.
 *
 * @ingroup wildlife_author
 *
 * @ContentEntityType(
 *   id = "author",
 *   label = @Translation("Author"),
 *   handlers = {
 *     "storage" = "Drupal\wildlife_author\AuthorStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wildlife_author\AuthorListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\wildlife_author\Form\AuthorForm",
 *       "add" = "Drupal\wildlife_author\Form\AuthorForm",
 *       "edit" = "Drupal\wildlife_author\Form\AuthorForm",
 *       "delete" = "Drupal\wildlife_author\Form\AuthorDeleteForm",
 *     },
 *     "access" = "Drupal\wildlife_author\AuthorAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\wildlife_author\AuthorHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "author",
 *   data_table = "author_field_data",
 *   revision_table = "author_revision",
 *   revision_data_table = "author_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer author entities",
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
 *     "canonical" = "/admin/structure/author/{author}",
 *     "add-form" = "/admin/structure/author/add",
 *     "edit-form" = "/admin/structure/author/{author}/edit",
 *     "delete-form" = "/admin/structure/author/{author}/delete",
 *     "version-history" = "/admin/structure/author/{author}/revisions",
 *     "revision" = "/admin/structure/author/{author}/revisions/{author_revision}/view",
 *     "revision_revert" = "/admin/structure/author/{author}/revisions/{author_revision}/revert",
 *     "translation_revert" = "/admin/structure/author/{author}/revisions/{author_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/author/{author}/revisions/{author_revision}/delete",
 *     "collection" = "/admin/structure/author",
 *   },
 *   field_ui_base_route = "author.settings"
 * )
 */
class Author extends RevisionableContentEntityBase implements AuthorInterface {

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

    // If no revision author has been set explicitly, make the author owner the
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
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Author entity.'))
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
      ->setLabel(t('Author name'))
      ->setDescription(t('The name of the Author.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['blog_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Blog identifier'))
      ->setDescription(t('A string used to construct the path of the Author\'s blog, e.g. blog/{blog-id}. Must be unique and can only contain lowercase letters, numbers and hyphens.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->addConstraint('UniqueField', []);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Author is published.'))
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
