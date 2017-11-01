<?php

namespace Drupal\wildlife_location\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Location edit forms.
 *
 * @ingroup wildlife_location
 */
class LocationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\wildlife_location\Entity\Location */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    // Add extra validation for the Name field.
    if (isset($form['name']['widget'][0]['value'])) {
      $form['name']['widget'][0]['value']['#element_validate'] = [[get_class($this), 'nameElementValidate']];
    }

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
        drupal_set_message($this->t('Created the %label Location.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Location.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.location.canonical', ['location' => $entity->id()]);
  }

  /**
   * Element validation helper.
   */
  public static function nameElementValidate($element, FormStateInterface $form_state) {
    // If the Admin name element value is empty, use the user facing Name value.
    if ($element['#value'] == '') {
      $frontend_name = $form_state->getValue('field_location_name');

      if ($frontend_name[0]['value'] != '') {
        $form_state->setValueForElement($element, $frontend_name[0]['value']);
      }
      else {
        // If the user facing Name value is empty, set an error.
        $form_state->setError($element, t('You must choose an "Admin name" or specify a "Name" to be used as the admin default.'));
      }
    }
  }

}
