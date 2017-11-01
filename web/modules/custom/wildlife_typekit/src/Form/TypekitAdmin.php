<?php

namespace Drupal\wildlife_typekit\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class TypekitAdmin extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wildlife_typekit_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'wildlife_typekit.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $typekit_config = $this->config('wildlife_typekit.settings');

    $form['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Typekit Kit ID'),
      '#description' => $this->t('The ID for the kit you want to use.'),
      '#default_value' => $typekit_config->get('id'),
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#open' => FALSE,
    ];

    $form['advanced']['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL'),
      '#default_value' => $typekit_config->get('base_url'),
      '#description' => $this->t('Do not include protocol or trailing slash. E.g. "https://use.typekit.net/" becomes "use.typekit.net"'),
    ];

    $form['advanced']['snippet'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Snippet'),
      '#default_value' => $typekit_config->get('snippet'),
      '#description' => $this->t('The typekit loading JS snippet.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory()->getEditable('wildlife_typekit.settings')
      ->set('id', $values['id'])
      ->set('base_url', $values['base_url'])
      ->set('snippet', $values['snippet'])

      ->save();

    Cache::invalidateTags(array('typekit'));

    parent::submitForm($form, $form_state);
  }
}
