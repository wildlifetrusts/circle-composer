<?php

namespace Drupal\wildlife_search;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class WildlifeSearchLocalApiFieldValues.
 *
 * @package Drupal\wildlife_search
 */
class WildlifeSearchLocalApiFieldValues {

  /**
   * HTTP Client.
   *
   * @var \GuzzleHttp\Client $httpClient
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Get the remote fields for a given UUID.
   *
   * @param $uuid
   *
   * @return array|null
   */
  public function getFields($uuid, $domain = NULL) {
    $remote_field_values = [];

    if ($domain) {
      $config_domains = [
        ['url' => $domain]
      ];
    }
    else {
      $config_domains = \Drupal::config('wildlife_sharing.settings')->get('domains');
    }

    foreach ($config_domains as $config_domain) {
      $file_contents = $this->httpClient->get($config_domain['url'] . '/localapi-fields/' . $uuid);
      // If we received a valid response, stop searching.
      if ($file_contents->getStatusCode() == '200') {
        // If we didn't find a valid file then stop processing.
        if (!empty($file_contents)) {
          $parsed = \GuzzleHttp\json_decode($file_contents->getBody());

          if (!empty($parsed)) {
            $remote_field_values['used_domain'] = $config_domain['url'];
            break;
          }
        }
      }
    }

    // If we didn't find a valid file then stop processing.
    if (empty($parsed)) {
      return NULL;
    }

    // Get the site language.
    $language = \Drupal::languageManager()->getCurrentLanguage()->getName();

    // Loop through each of the returned item translations.
    foreach ($parsed as $key => $translation) {
      // If a translation exists for the current language, use it.
      if ($translation->langcode == $language) {
        $item = $parsed[$key];
      }
    }

    // If there is no translation for the given item and language, just return
    // the first parsed item.
    if (!isset($item)) {
      $item = reset($parsed);
    }

    // If the parsed result is empty then the entity doesn't exist on
    // the remote site so return NULL.
    if (empty($item)) {
      return NULL;
    }

    $remote_field_values['item'] = $item;

    return $remote_field_values;
  }
}
