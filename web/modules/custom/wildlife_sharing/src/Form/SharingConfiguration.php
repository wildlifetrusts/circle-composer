<?php

namespace Drupal\wildlife_sharing\Form;

use \Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SharingConfiguration extends FormBase {
  /** {@inheritdoc} */
  public function getFormId() {
    return 'wildlife_sharing_configuration';
  }

  /** {@inheritdoc} */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $domains = [];

    $config_domains = \Drupal::config('wildlife_sharing.settings')->get('domains');
    if (!empty($config_domains)) {
      $domains = array_merge($domains, $config_domains);
    }

    $state_domains = $form_state->get('domains');
    if (!empty($state_domains)) {
      $domains = array_merge($domains, $state_domains);
    }

    $minimum = 3;
    $state_count = $form_state->get('domain_count');
    $count = max(count($domains) + 1, $minimum, $state_count);


    $form['domains'] = [
      '#count' => $count,
      '#tree' => TRUE,
    ];
    for ($i = 0; $i < $count; $i++) {
      $form['domains'][$i] = [
        '#type' => 'fieldset',
        'title' => [
          '#title' => $this->t('Title'),
          '#type' => 'textfield',
          '#default_value' => !empty($domains[$i]) && !empty($domains[$i]['title']) ? $domains[$i]['title'] : NULL,
        ],
        'url' => [
          '#title' => $this->t('URL'),
          '#type' => 'textfield',
          '#default_value' => !empty($domains[$i]) && !empty($domains[$i]['url']) ? $domains[$i]['url'] : NULL,
        ],
        'email' => [
          '#title' => $this->t('Email'),
          '#type' => 'textfield',
          '#default_value' => !empty($domains[$i]) && !empty($domains[$i['email']]) ? $domains[$i]['email'] : NULL,
        ],
      ];
    }

    $form['add_another'] = [
      '#type' => 'submit',
      '#submit' => [[get_class($this), 'addMoreSubmit']],
      '#value' => $this->t('Add another'),
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
  public static function addMoreSubmit(array &$form, FormStateInterface $form_state) {
    // Increment the items count.
    $form_state->set('domain_count', count($form_state->getValue('domains')) + 1);

    $form_state->setRebuild();
  }

  /** {@inheritdoc} */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the setting.
    $config_factory = \Drupal::service('config.factory');

    $config = $config_factory->getEditable('wildlife_sharing.settings');
    $domains = $form_state->getValue('domains');
    // Filter out settings with titles but no URLs.
    $domains = array_filter($domains, function ($item) {
      return !(empty($item['title']) && empty($item['url']));
    });
    $config->set('domains', $domains)->save();

    drupal_set_message('Configuration saved');
  }
}
