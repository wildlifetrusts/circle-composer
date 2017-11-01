<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\FocalPoint.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\file\Entity\File;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_focal_point"
 * )
 */
class FocalPoint extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($value)) {
      list($x, $y) = explode(',', $value);
      if ($x && $y) {
        $factory = \Drupal::service('image.factory');
        $target = File::load($row->getDestination()['field_media_image']['target_id']);

        $height = $factory->get($target->getFileUri())->getHeight();
        $width = $factory->get($target->getFileUri())->getWidth();
        $relative = $this->absoluteToRelative($x, $y, $width, $height);
        return implode(',', $relative);
      }
    }
    return NULL;
  }

  /**
   * Helper function to convert absolute values to relative.
   *
   * @see \Drupal\wildlife_sharing\Plugin\migrate\process\FocalPoint::absoluteToRelative().
   */
  protected function absoluteToRelative($x, $y, $width, $height) {
    return [
      'x' => (int) round($x / $width * 100),
      'y' => (int) round($y / $height * 100),
    ];
  }
}

