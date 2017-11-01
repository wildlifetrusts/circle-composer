<?php

namespace Drupal\wildlife_google_api\Form;

use \Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class GoogleApiConfiguration extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wildlife_google_api_configuration';
  }

  /** {@inheritdoc} */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $google_api_settings = \Drupal::config('wildlife_google_api.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google API Key'),
      '#default_value' => !empty($google_api_settings->get('api_key')) ? $google_api_settings->get('api_key') : NULL,
      '#description' => t('The API key should have be set up to support: Google Maps Geocoding API, Google Maps JavaScript API, and Google Static Maps API.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the current settings.
    $config_factory = \Drupal::service('config.factory');
    /** @var \Drupal\Core\Config\Config $config */
    $config = $config_factory->getEditable('wildlife_google_api.settings');

    // Save the API key.
    $api_key = $form_state->getValue('api_key');

    $config
      ->set('api_key', $api_key)
      ->save();

    // Update the Maps API key for Geofield Map.
    $location_entity_form_display = $config_factory->getEditable('geofield_map.settings');
    $location_entity_form_display
      ->set('gmap_api_key', $api_key)
      ->save();

    drupal_set_message('Configuration saved');
  }
}
