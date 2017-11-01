<?php

namespace Drupal\wildlife_seo\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class GtmIdAdmin extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wildlife_seo_gtm_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'wildlife_seo.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $gtm_config = $this->config('wildlife_seo.settings');

    $form['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container ID'),
      '#description' => $this->t('The ID assigned by Google Tag Manager (GTM) for this website container. To get a container ID, <a href="https://tagmanager.google.com/">sign up for GTM</a> and create a container for your website.'),
      '#default_value' => !empty($gtm_config->get('gtm.id')) ? $gtm_config->get('gtm.id') : '',
      '#attributes' => ['placeholder' => ['GTM-XXXX']],
      '#size' => 12,
      '#maxlength' => 15,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory()->getEditable('wildlife_seo.settings')
      ->set('gtm', ['id' => $values['id']])
      ->save();

    Cache::invalidateTags(array('wildlife_seo_gtm'));

    parent::submitForm($form, $form_state);
  }
}
