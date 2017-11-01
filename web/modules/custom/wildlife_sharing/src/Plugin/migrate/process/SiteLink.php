<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\SiteLink.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\Core\Url;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\MigrationLookup;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate_tools\MigrateExecutable;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_site_link"
 * )
 */
class SiteLink extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!$migrate_executable instanceof MigrateExecutable) {
      return NULL;
    }

    $url = $row->getSourceProperty('active_url');
    $parsed = parse_url($url['url']);
    if (empty($parsed['scheme']) || empty($parsed['host'])) {
      return [];
    }
    $uri = $parsed['scheme'] . '://' . $parsed['host'] . '/node/' . $row->getSourceProperty('nid');
    return [
      'uri' => $uri,
      'title' => $url['title'],
      'options' => [],
    ];
  }
}

