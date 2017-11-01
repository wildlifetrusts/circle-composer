<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;

/**
 * Plugin implementation of the 'image_gallery_item' formatter.
 *
 * @FieldFormatter(
 *   id = "image_gallery_item",
 *   label = @Translation("Image gallery item"),
 *   field_types = {
 *     "entity_reference_revisions",
 *     "entity_reference",
 *   }
 * )
 */
class ImageGalleryItem extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $paragraphs = $items->referencedEntities();
    $backgrounds_service = \Drupal::service('wildlife_backgrounds.backgrounds_service');

    foreach ($paragraphs as $delta => $item) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph_parent */
      $paragraph_parent = $items->getParent()->getValue();

      if ($paragraph_parent->hasField('field_gallery_image') && $paragraph_parent->hasField('field_gallery_image_caption')) {
        $image = $paragraph_parent->get('field_gallery_image');
        $caption = $paragraph_parent->get('field_gallery_image_caption');

        if (!$image->isEmpty()) {
          $image_values = $image->referencedEntities()[0];
          /** @var FileFieldItemList $image_values_field */
          $image_values_field = $image_values->get('field_media_image')->getValue();
          $image_alt = $image_values_field[0]['alt'];
        }

        // Set up the Thumbnail attributes and responsive images.
        $gallery_thumbs = $backgrounds_service->getResponsiveBackgroundImages($image, 'gallery_image');

        $thumb_attributes = [
          'data-lazy' => $gallery_thumbs['default'],
          'draggable' => 'true',
          'aria-dropeffect' => 'move',
          'alt' => isset($image_alt) ? $image_alt : $this->t('Gallery image'),
        ];

        foreach ($gallery_thumbs as $key => $image_style) {
          $thumb_attributes['data-lazy-responsive-' . $key] = $image_style;
        }

        // Set up the Full size attributes and responsive images.
        $explicit_styles = [
          'default' => 'scaled_default',
        ];

        $gallery_images = $backgrounds_service->getResponsiveBackgroundImages($image, 'scaled_12_col', $explicit_styles);
        $attributes = [
          'data-cbox-img-attrs' => isset($image_alt) ? '{"alt":"' . $image_alt . '"}' : '{"alt":"' . $this->t('Gallery image') . '"}',
        ];

        foreach ($gallery_images as $key => $image_style) {
          $attributes['data-full-' . $key] = $image_style;
        }

        $image = [
          '#theme' => 'image',
          '#attributes' => $thumb_attributes,
        ];

        if (!$caption->isEmpty()) {
          $attributes['title'] = $caption->getString();
        }

        $elements[$delta] = [
          '#type' => 'link',
          '#title' => $image,
          '#url' => Url::fromUri($gallery_images['default']),
          '#options' => array(
            'attributes' => $attributes,
            'html' => TRUE,
          ),
        ];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available on image_gallery bundles.
    $bundle = $field_definition->getTargetBundle();

    return $bundle == 'image_gallery_item';
  }
}
