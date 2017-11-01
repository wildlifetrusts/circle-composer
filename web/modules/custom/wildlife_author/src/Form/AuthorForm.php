<?php

namespace Drupal\wildlife_author\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wildlife_author\AuthorStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Author edit forms.
 *
 * @ingroup wildlife_author
 */
class AuthorForm extends ContentEntityForm {

  /**
   * The View header storage.
   *
   * @var \Drupal\wildlife_author\AuthorStorageInterface
   */
  protected $authorStorage;

  /**
   * Constructs a new vocabulary form.
   *
   * @param \Drupal\wildlife_author\AuthorStorageInterface $author_storage
   *   The author storage.
   */
  public function __construct(AuthorStorageInterface $author_storage, EntityManagerInterface $entity_manager) {
    parent::__construct($entity_manager);
    $this->authorStorage = $author_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('author'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\wildlife_author\Entity\Author */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    // Update the Blog ID widget to be a machine name.
    $form['blog_id']['widget'][0]['value']['#type'] = 'machine_name';
    $form['blog_id']['widget'][0]['value']['#machine_name'] = [
      'exists' => [$this, 'exists'],
      'source' => ['name', 'widget', 0, 'value'],
      'label' => 'Blog identifier',
      'replace_pattern' => '[^a-z0-9-]+',
      'replace' => '-',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Author.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Author.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.author.collection');
  }

  /**
   * Determines if the blog id already exists.
   *
   * @param string $blog_id
   *   The Blog ID.
   *
   * @return bool
   *   TRUE if the blog id exists, FALSE otherwise.
   */
  public function exists($blog_id) {
    $action = $this->authorStorage->getAuthorByBlogId($blog_id);
    return !empty($action);
  }

}
