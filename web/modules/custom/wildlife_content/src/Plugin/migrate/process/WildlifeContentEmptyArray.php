<?php
/**
 * @file
 * Contains \Drupal\wildlife_content\Plugin\migrate\process\HiscoxExampleContentEmptyArray.
 */

namespace Drupal\wildlife_content\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Transform term name to tid.
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_content_empty_array"
 * )
 */
class WildlifeContentEmptyArray extends ProcessPluginBase {
  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->multiple = FALSE;

    if (empty($value)) {
      return array();
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple () {
    return $this->multiple;
  }

}

