<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\IdEntityLookup.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_remote_term_lookup"
 * )
 */
class RemoteTermLookup extends EntityLookup {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = !empty($value['id']) ? $value['id'] : $value;

    $http_client = \Drupal::getContainer()->get('http_client');
    $active_url = $row->getSource()['active_url'];
    $domain = explode('/jsonapi', $active_url['url'])[0];
    $type = $this->configuration['bundle'];
    $file_contents = $http_client->get($domain . '/jsonapi/taxonomy_term/' . $type . '/' . $value);
    if ($file_contents->getStatusCode() == '200') {
      $parsed = \GuzzleHttp\json_decode($file_contents->getBody());
      if (!empty($parsed) && !empty($parsed->data) && !empty($parsed->data->attributes) && !empty($parsed->data->attributes->name)) {
        $value = $parsed->data->attributes->name;
      }
    }
    $return = parent::transform($value, $migrate_executable, $row, $destination_property);
    return $return;
  }
}

