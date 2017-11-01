<?php

namespace Drupal\wildlife_sharing\Normalizer;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Url;
use Drupal\crop\Entity\Crop;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\jsonapi\Normalizer\ContentEntityNormalizer;
use Drupal\jsonapi\ResourceType\ResourceType;

/**
 * Class ImageLinkNormalizer
 *
 * @package Drupal\wildlife_sharing\Normalizer
 */
class ImageLinkNormalizer extends ContentEntityNormalizer {
  public function getFields($entity, $bundle, ResourceType $resource_type) {
    $fields = parent::getFields($entity, $bundle, $resource_type);

    // We only want to add focal point field for images.
    if ($bundle != 'image') {
      return $fields;
    }

    // Find any File fields, look for an associated Crop (used by Focal Point)
    // and add it to the output fields array.
    foreach ($fields as $name => $field_item_list) {
      if ($name != 'field_image_link_url') {
        continue;
      }
      if ($field_item_list instanceof FieldItemList) {
        foreach ($field_item_list as $field) {
          $value = $field->getValue();
          if ($value && !empty($value['uri']) && strpos($value['uri'], 'entity:') !== FALSE) {
            $url = Url::fromUri($value['uri'], ['absolute' => TRUE]);
            $value['uri'] = $url->toString();
            $fields['field_image_link_url']->setValue($value);
          }

        }
      }
    }

    return $fields;
  }
}
