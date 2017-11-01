<?php

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\FileCopy;
use Drupal\migrate\Row;

/**
 * Import a file as a side-effect of a migration.
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_media_save"
 * )
 */
class MediaSave extends FileCopy {
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $url = $row->getSourceProperty('active_url');
    $parsed = parse_url($url['url']);
    $value[0] = $parsed['scheme'] . '://' . $parsed['host'] . $value[0];
    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }
}
