<?php

namespace Drupal\wildlife_sharing\Normalizer;

use Drupal\crop\Entity\Crop;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\jsonapi\Normalizer\ContentEntityNormalizer;
use Drupal\jsonapi\ResourceType\ResourceType;

/**
 * Class FocusPointEntityNormalizer
 *
 * @package Drupal\wildlife_sharing\Normalizer
 */
class FocusPointEntityNormalizer extends ContentEntityNormalizer {
  public function getFields($entity, $bundle, ResourceType $resource_type) {
    $fields = parent::getFields($entity, $bundle, $resource_type);

    // We only want to add focal point field for images.
    if ($bundle != 'image') {
      return $fields;
    }

    // Find any File fields, look for an associated Crop (used by Focal Point)
    // and add it to the output fields array.
    foreach ($fields as $name => $field_item_list) {
      if ($field_item_list instanceof FileFieldItemList) {
        foreach ($field_item_list as $field) {
          $value = $field->getValue();
          if ($value && !empty($value['target_id']) && $file = File::load($value['target_id'])) {
            $crop_type = \Drupal::config('focal_point.settings')->get('crop_type');
            $crop = Crop::findCrop($file->getFileUri(), $crop_type);
          }
          if (empty($crop)) {
            $crop = new Crop([], 'crop');
          }
          $crop_fields = $crop->getFields();
          // Copy entity_type to focal_point (as it is a string based value).
          $fields['focal_point'] = $crop_fields['entity_type'];
          if (!$fields['focal_point']->isEmpty()) {
            $fields['focal_point']->setValue(NULL);
          }
          // Ensure value is NULL by default.
          if (!$crop_fields['x']->isEmpty() && !$crop_fields['y']->isEmpty()) {
            $x_value = $crop_fields['x']->first()->getValue()['value'];
            $y_value = $crop_fields['y']->first()->getValue()['value'];
            $fields['focal_point']->setValue($x_value . ',' . $y_value);
          }
        }
      }
    }

    return $fields;
  }
}
