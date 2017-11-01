<?php

namespace Drupal\wildlife_sharing\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\migrate\source\Url;

/**
 * Source plugin for retrieving data via URLs.
 *
 * @MigrateSource(
 *   id = "wildlife_sharing_url_title"
 * )
 */
class UrlTitle extends Url {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, \Drupal\migrate\Plugin\MigrationInterface $migration) {
    $config_factory = \Drupal::service('config.factory');
    $domains = $config_factory->get('wildlife_sharing.settings')->get('domains');
    foreach ($domains as &$domain) {
      $domain['url'] .= $configuration['path'];
    }
    $configuration['urlTitles'] = $domains;
    $configuration['urls'] = array_column($domains, 'url');
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }
}
