<?php

namespace Drupal\wildlife_backgrounds;

use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Class Silhouette.
 *
 * @package Drupal\wildlife_backgrounds
 */
class BackgroundsService {

  /**
   * @param \Drupal\Core\Field\EntityReferenceFieldItemList $field
   *   An entity reference field for a Silhouette.
   *
   * @return \Drupal\Core\GeneratedUrl|string
   *   The URL or an empty string.
   */
  public function getSilhouetteUrl($field) {
    $silhouette_file = '';

    if (!empty($field->getValue())) {
      $silhouette_image_media_item = $field->referencedEntities()[0];
      $silhouette_image_media_value = $silhouette_image_media_item->get('field_silhouette')
        ->first()
        ->getValue();
      $silhouette_media_target_id = $silhouette_image_media_value['target_id'];
      $silhouette_file = File::load($silhouette_media_target_id)->url();
    }

    return $silhouette_file;
  }

  /**
   * @param \Drupal\Core\Field\EntityReferenceFieldItemList $field
   *   An entity reference field for a Background image.
   *
   * @param string $image_style_prefix
   *   The name of the image style prefix, defaults to hero.
   *
   * @param array $explicit_styles
   *   An array of explicit styles to give for background viewports.
   *
   * @return array
   *   An array of image styles for use with responsive images.
   */
  public function getResponsiveBackgroundImages($field, $image_style_prefix = 'hero', $explicit_styles = []) {
    if (empty($field->referencedEntities())) {
      return [];
    }
    $background_image_media_item = $field->referencedEntities()[0];
    $background_image_media_value = $background_image_media_item->get('field_media_image');
    
    if ($background_image_media_value->isEmpty()) {
      return [];
    }
    $background_image_media_value = $background_image_media_value->first()
      ->getValue();
    $background_media_target_id = $background_image_media_value['target_id'];
    $file = File::load($background_media_target_id);

    if (!$file) {
      return [];
    }

    $backgrounds = [];

    $viewports = ['default', 'palm', 'lap', 'desk', 'desk_wide'];
    $image_styles = ImageStyle::loadMultiple();

    foreach ($viewports as $viewport) {
      $style_name = in_array($viewport, array_keys($explicit_styles)) ? $explicit_styles[$viewport] : $image_style_prefix . '_' . $viewport;

      if (isset($image_styles[$style_name])) {
        $backgrounds[$viewport] = ImageStyle::load($style_name)
          ->buildUrl($file->getFileUri());
      }
    }

    return $backgrounds;
  }
}
