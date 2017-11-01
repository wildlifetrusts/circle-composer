<?php

namespace Drupal\wildlife_sharing\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api_views_taxonomy\Plugin\views\filter\SearchApiTerm;

/**
 * Class SearchApiTermRemote
 *
 * Based on \Drupal\search_api_views_taxonomy\Plugin\views\filter\SearchApiTerm.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("search_api_term_remote")
 */
class SearchApiTermRemote extends SearchApiTerm {
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $remote_vocabularies = [
      'event_type',
      'tag_categories',
      'job_type',
      'opportunity_type',
    ];

    if (!\Drupal::moduleHandler()->moduleExists('wildlife_local_customisation')) {
      return;
    }
    $vocabulary = $this->configuration['vocabulary'];
    if (!in_array($vocabulary, $remote_vocabularies)) {
      return;
    }

    $config_domains = \Drupal::config('wildlife_sharing.settings')->get('domains');

    foreach ($config_domains as $config_domain) {
      /** @var \GuzzleHttp\Client $httpClient */
      $httpClient = \Drupal::service('http_client');
      $file_contents = $httpClient->get($config_domain['url'] . '/jsonapi/taxonomy_term/' . $vocabulary);
      // If we received a valid response, stop searching.
      if ($file_contents->getStatusCode() == '200') {
        break;
      }
    }

    // If we didn't find a valid file then stop processing.
    if (empty($file_contents)) {
      return NULL;
    }
    $parsed = \GuzzleHttp\json_decode($file_contents->getBody());

    if (empty($parsed->data)) {
      return NULL;
    }

    $options = [];
    foreach ($parsed->data as $term) {
      $options[$term->attributes->tid] = $term->attributes->name;
    }

    $form['value']['#options'] = $options;
  }
}
