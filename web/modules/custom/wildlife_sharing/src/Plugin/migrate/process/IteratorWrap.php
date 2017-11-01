<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\IteratorWrap.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\Iterator;
use Drupal\migrate\Row;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_iterator_wrap"
 * )
 */
class IteratorWrap extends Iterator {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = [];
    if (!is_null($value)) {
      foreach ($value as $key => $new_value) {
        if (is_numeric($key)) {
          $new_row = new Row($new_value, []);
          $migrate_executable->processRow($new_row, $this->configuration['process']);
          $destination = $new_row->getDestination();
          if (array_key_exists('key', $this->configuration)) {
            $key = $this->transformKey($key, $migrate_executable, $new_row);
          }
          $return[$key] = $destination;
        }
        else {
          $return[$key] = $new_value;
        }
      }
    }
    return $return;
  }

  public function multiple() {
    return FALSE;
  }
}

