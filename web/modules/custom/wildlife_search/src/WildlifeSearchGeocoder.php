<?php

namespace Drupal\wildlife_search;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Drupal\geocoder\Geocoder;

/**
 * Provides a geocoder factory class.
 */
class WildlifeSearchGeocoder extends Geocoder {
  /**
   * {@inheritdoc}
   */
  public function geocode($data, array $plugins, array $options = []) {
    foreach ($plugins as $plugin_id) {
      $options += [$plugin_id => []];
      $provider = $this->providerPluginManager->createInstance($plugin_id, $options[$plugin_id]);

      try {
        return $provider->geocode($data);
      }
      catch (InvalidCredentials $e) {
        static::log($e->getMessage(), 'error');
      }
      catch (NoResult $e) {
        $message = t('Sorry, there were no location results found for "@data".', ['@data' => $data])->render();
        static::log($message, 'error');
      }
      catch (\Exception $e) {
        static::log($e->getMessage(), 'error');
      }
    }

    return FALSE;
  }

}
