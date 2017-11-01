<?php
/**
 * @file
 * Include file for Wildlife Paragraph preprocess functions.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Helper function to find information about a group of spotlights.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $spotlight_group_paragraph
 *   A paragraph entity.
 * @return array
 *   An array of useful information about a Spotlight.
 */
function _wildife_paragraphs_retrieve_spotlight_information($spotlight_group_paragraph) {
  $spotlight_information = [];

  $spotlights = $spotlight_group_paragraph->get('field_spotlights');
  $spotlight_values = $spotlights->getValue();

  if (!empty($spotlight_values)) {
    $spotlight_information['count'] = count($spotlight_values);
    $spotlight_entities = $spotlights->referencedEntities();
    $group_contains_separate = FALSE;

    foreach ($spotlight_entities as $entity) {
      $display_type = $entity->get('field_spotlight_display_type')->getString();

      if ($display_type == 'separate') {
        $group_contains_separate = TRUE;
        break;
      }
    }

    $spotlight_information['group_contains_separate'] = $group_contains_separate;
    $spotlight_information['cardinality_class'] = _wildlife_paragraphs_spotlight_cardinality_class($spotlight_information);
  }

  return $spotlight_information;
}



/**
 * Helper function to get the cardinality class.
 *
 * @param array $spotlight_information
 *   An array of useful information about a Spotlight.
 * @return string
 *   The cardinality class.
 */
function _wildlife_paragraphs_spotlight_cardinality_class($spotlight_information) {
  $class = '';
  $spotlight_count = $spotlight_information['group_contains_separate'] ? 1 : $spotlight_information['count'];

  if ($spotlight_count > 0 && $spotlight_count < 5) {
    $cardinality_classes = [
      1 => 'single',
      2 => 'double',
      3 => 'triple',
      4 => 'quadruple',
    ];

    $class = $cardinality_classes[$spotlight_count];
  }

  return $class;
}


/**
 * Helper function to preprocess Linked Logo Item paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_linked_logo_item(&$variables, $paragraph) {
  // Find out if there's a link specified.
  $variables['wrapper_tag'] = 'div';
  $has_link = !empty($paragraph->get('field_linked_logo_item_link')->first());

  // If so, get the URI value and convert to a URL.
  if ($has_link && !$paragraph->get('field_linked_logo_item_link')->first()->isEmpty()) {
    $variables['wrapper_tag'] = 'a';
    $variables['attributes']['href'] = $paragraph->get('field_linked_logo_item_link')->first()->getUrl()->toString();
    $link_options = $paragraph->get('field_linked_logo_item_link')->first()->get('options')->getValue();
    $link_attributes = isset($link_options['attributes']) ? $link_options['attributes'] : [];
    $variables['attributes'] = array_merge($variables['attributes'], $link_attributes);
  }
}

/**
 * Helper function to preprocess Spotlight Group paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_spotlight_group(&$variables, $paragraph) {
  if ($variables['view_mode'] != 'preview') {
    $spotlight_information = _wildife_paragraphs_retrieve_spotlight_information($paragraph);

    $variables['attributes']['class'][] = 'spotlights';
    $variables['attributes']['class'][] = 'breakout';

    $spotlights = $paragraph->get('field_spotlights');
    $spotlight_values = $spotlights->getValue();

    foreach ($spotlight_values as $key => $spotlight) {
      $variables['content']['field_spotlights'][$key]['#parent_cardinality'] = $spotlight_information['cardinality_class'];
    }

    if (isset($spotlight_information['group_contains_separate']) && isset($spotlight_information['cardinality_class'])) {
      if (!$spotlight_information['group_contains_separate']) {
        $variables['attributes']['class'][] = 'spotlights--' . $spotlight_information['cardinality_class'];
      }
    }
  }
}

/**
 * Helper function to preprocess Spotlight paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_spotlight(&$variables, $paragraph) {
  if ($variables['view_mode'] != 'preview') {
    // Link preprocessing.
    $variables['inner_tag'] = 'div';
    $inner_attributes = ['class' => 'inner'];
    $link = $paragraph->get('field_spotlight_link')->getValue();

    if (!empty($link)) {
      $link_appearance = $paragraph->get('field_spotlight_link_appearance')->first()->getString();
      $variables['block_link'] = $link_appearance == 'block';

      if ($variables['block_link']) {
        $variables['inner_tag'] = 'a';
        $inner_attributes['href'] = Url::fromUri($link[0]['uri'])->toString();
        $inner_attributes['title'] = $link[0]['title'];

        if (!empty($link[0]['options'])) {
          $inner_attributes = array_merge($inner_attributes, $link[0]['options']['attributes']);
        }
      }
      else {
        $link_appearance = $paragraph->get('field_spotlight_button_type')->first()->getString();
        $variables['content']['field_spotlight_link'][0]['#options']['attributes']['class'][] = 'button';
        $variables['content']['field_spotlight_link'][0]['#options']['attributes']['class'][] = 'button--' . $link_appearance;

        if ($link_appearance == 'secondary') {
          $variables['content']['field_spotlight_link'][0]['#options']['attributes']['class'][] = 'button--contrast';
        }
      }
    }

    $variables['inner_attributes'] = array_filter($inner_attributes);

    // Display.
    $display_type = $paragraph->get('field_spotlight_display_type')->first()->getString();
    $variables['attributes']['class'][] = 'spotlight--' . $display_type;
    $variables['spotlight_display'] = $display_type;

    $text_colour = $paragraph->get('field_spotlight_text_colour')->first()->getString();
    $variables['attributes']['class'][] = 'spotlight--' . $text_colour;

    if (isset($variables['elements']['#parent_cardinality'])) {
      $cardinality_class = $variables['elements']['#parent_cardinality'];
      $variables['attributes']['class'][] = 'spotlight--' . $cardinality_class;
    }

    // Background.
    $image_type = $paragraph->get('field_spotlight_image_type')->first()->getString();
    $variables['spotlight_silhouette'] = FALSE;
    $variables['#attached']['library'][] = 'wildlife_trust/spotlight-animatable-background';

    if ($image_type == 'image' && $display_type == 'overlay') {
      $background_image = $paragraph->get('field_spotlight_image');

      if (!empty($background_image->getValue())) {
        $backgrounds_service = \Drupal::service('wildlife_backgrounds.backgrounds_service');
        $image_style_prefix = isset($cardinality_class) ? 'spotlight_' . $cardinality_class : 'spotlight_single';
        $explicit_styles = [
          'default' => 'spotlight_default',
          'palm' => 'spotlight_palm',
        ];
        $backgrounds = $backgrounds_service->getResponsiveBackgroundImages($background_image, $image_style_prefix, $explicit_styles);

        // Add the default background style and loop through the other
        // backgrounds to add those.
        if (isset($backgrounds['default'])) {
          $variables['attributes']['style'] = 'background-image: url(' . $backgrounds['default'] . ');';
        }

        foreach ($backgrounds as $viewport => $background) {
          $variables['attributes']['data-background-' . $viewport] = $background;
        }
      }
    }
    else if ($image_type == 'silhouette') {
      $silhouette_image = $paragraph->get('field_spotlight_silhouette');
      if (!empty($silhouette_image->getValue())) {
        $silhouette_image_media_item = $silhouette_image->referencedEntities()[0];
        $silhouette_image_media_value = $silhouette_image_media_item->get('field_silhouette');

        if (!$silhouette_image_media_value->isEmpty()) {
          $silhouette_image_media_value = $silhouette_image_media_value->first()->getValue();

          $silhouette_media_target_id = $silhouette_image_media_value['target_id'];
          $silhouette_file = File::load($silhouette_media_target_id)->url();
          $variables['spotlight_silhouette'] = $silhouette_file;
          $variables['attributes']['class'][] = 'spotlight--silhouette';
        }
      }
    }
  }
}

/**
 * Helper function to preprocess Statistic Item paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_statistic_item(&$variables, $paragraph) {
  $variables['wrapper_element'] = 'div';

  if(!$paragraph->get('field_statistic_type')->isEmpty()) {
    $statistic_type = $paragraph->get('field_statistic_type')->first()->getString();
    $statistic_link = $paragraph->get('field_statistic_url')->first();

    if (!empty($statistic_link)) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $statistic_link_value */
      $statistic_link_value = $statistic_link->getValue();
      $url = Url::fromUri($statistic_link_value['uri'])->toString();
      $link_attributes = ['href' => $url];
      if (isset($statistic_link_value['options']['attributes'])) {
        $link_attributes = array_merge($statistic_link_value['options']['attributes'], $link_attributes);
      }

      $variables['attributes'] = array_merge($variables['attributes'], $link_attributes);
      $variables['wrapper_element'] = 'a';
    }

    switch ($statistic_type) {
      case 'dynamic':
        if ($paragraph->get('field_statistic_dynamic_type')->isEmpty()) {
          break;
        }

        /** @var \Drupal\Core\Field\FieldItemList $static_type */
        $dynamic_type_value = $paragraph->get('field_statistic_dynamic_type')->first()->getString();

        // Set the dynamic icon.
        $dynamic_icons = [
          'reserves' => 'signpost',
          'events' => 'calendar',
        ];

        $variables['icon'] = 'stat-' . $dynamic_icons[$dynamic_type_value];

        $text = '';
        $statistics_count = Drupal::service('wildlife_paragraphs.statistics_count');

        switch ($dynamic_type_value) {
          case 'events':
            $count = $statistics_count->getUpcomingEvents();
            $text = $statistics_count->getDynamicStatText($dynamic_type_value, $count);

            break;

          case 'reserves':
            $count = $statistics_count->getReserves();
            $text = $statistics_count->getDynamicStatText($dynamic_type_value, $count);
            break;
        }

        // Amount is not used for dynamic counts since they are part of the
        // text. So this is set to an empty string.
        $variables['amount'] = '';

        // Set the dynamic text.
        $variables['text'] = $text;
        break;

      default:
        /** @var \Drupal\Core\Field\FieldItemList $static_type */
        $static_type = $paragraph->get('field_statistic_static_type');

        if ($static_type->isEmpty()) {
          break;
        }

        $static_type_value = $static_type->first()->getString();

        // Set the static icon.
        $variables['icon'] = 'stat-' . $static_type_value;

        // Set the static amount.
        $variables['amount'] = '';

        if (!$paragraph->get('field_statistic_static_number')->isEmpty()) {
          $amount_value = $paragraph->get('field_statistic_static_number')->first()->getString();
          $variables['amount'] = number_format($amount_value);
        }

        // Set the static text.
        $custom_text_field = $paragraph->get('field_statistic_static_custom')->first();

        if (!empty($custom_text_field)) {
          $variables['text'] = t($custom_text_field->getString());
        }
        else {
          $static_type_allowed_values = $static_type->getItemDefinition()->getSetting('allowed_values');
          $variables['text'] = $static_type_allowed_values[$static_type_value];
        }

        break;
    }
  }
}

/**
 * Helper function to preprocess Image Gallery Item paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_image_gallery_item(&$variables, $paragraph) {
  if ($variables['view_mode'] == 'default') {
    $backgrounds_service = \Drupal::service('wildlife_backgrounds.backgrounds_service');
    $image = $paragraph->get('field_gallery_image');
    $caption = $paragraph->get('field_gallery_image_caption');

    // Set up the Full size attributes and responsive images.
    $explicit_styles = [
      'default' => 'scaled_default',
    ];

    $gallery_images = $backgrounds_service->getResponsiveBackgroundImages($image, 'scaled_12_col', $explicit_styles);
    $attributes = [
      'href' => Url::fromUri($gallery_images['default'])->toString(),
    ];

    foreach ($gallery_images as $key => $image_style) {
      $attributes['data-full-' . $key] = $image_style;
    }

    if (!$caption->isEmpty()) {
      $attributes['title'] = $caption->getString();
    }

    $variables['attributes'] = $attributes;
  }
}

/**
 * Helper function to preprocess Image paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_image(&$variables, $paragraph) {
  $link = $paragraph->get('field_image_link_url');
  // Do nothing if the link field is empty.
  if ($link->isEmpty()) {
    return;
  }
  $link = $link->first()->getValue();
  if (empty($link['uri'])) {
    return;
  }

  // Add a link uri array key so that media preprocessing can wrap the image
  // (and not the caption) in a link.
  $variables['content']['field_image'][0]['#link_uri'] = $link['uri'];
}

/**
 * Helper function to preprocess Headline Hero paragraphs.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_paragraph().
 *
 * @param $paragraph
 *  An array of the current paragraph.
 */
function _wildlife_paragraphs_preprocess_headline_hero(&$variables, $paragraph) {
  if ($variables['view_mode'] != 'preview') {
    // Set all booleans to be false by default.
    $variables['is_image'] = FALSE;
    $variables['is_silhouette'] = FALSE;
    $variables['is_video'] = FALSE;

    // Get the type of background that should display on the hero.
    $background_type = !empty($paragraph->get('field_headline_hero_background')
      ->first())
      ? $paragraph->get('field_headline_hero_background')->first()->getString()
      : NULL;

    if (in_array($background_type, ['image', 'silhouette'])) {
      $backgrounds_service = Drupal::service('wildlife_backgrounds.backgrounds_service');

      switch ($background_type) {
        // If the hero type is 'image', add a url to image so it can be output
        // as a background image in the template.
        case 'image':
          $variables['is_image'] = TRUE;
          $background_image = $paragraph->get('field_headline_hero_image');
          $backgrounds = $backgrounds_service->getResponsiveBackgroundImages($background_image);

          if (!empty($backgrounds)) {
            // Add the default background style and loop through the other
            // backgrounds to add those.
            $variables['attributes']['style'] = 'background-image: url(' . $backgrounds['default'] . ');';

            foreach ($backgrounds as $viewport => $background) {
              $variables['attributes']['data-background-' . $viewport] = $background;
            }
          }
          break;

        case 'silhouette':
          $variables['is_silhouette'] = TRUE;
          $silhouette_image = $paragraph->get('field_headline_hero_silhouette');
          $silhouette_file = $backgrounds_service->getSilhouetteUrl($silhouette_image);

          if (!empty($silhouette_file)) {
            $variables['headline_hero_silhouette'] = $silhouette_file;
            $variables['attributes']['class'][] = 'headline-hero--silhouette';
          }
          break;
      }
    }

    // Check if the video field has a value.
    $background_video = $paragraph->get('field_headline_hero_video');
    $has_video = !empty($background_video->getValue());
    $variables['has_video'] = $has_video;

    if ($has_video) {
      // Get the URL of the video.
      $background_video_media_item = $background_video->referencedEntities()[0];
      $background_video_media_value = $background_video_media_item->get('field_media_video')
        ->first()
        ->getValue()['value'];

      // Get the video's dimensions.
      $width = $background_video_media_item->get('thumbnail')
        ->first()
        ->getValue()['width'];
      $height = $background_video_media_item->get('thumbnail')
        ->first()
        ->getValue()['height'];

      // Find out whether the video is on YouTube or Vimeo.
      $is_youtube = preg_match('/youtube/', $background_video_media_value);
      $is_vimeo = preg_match('/vimeo/', $background_video_media_value);
      $variables['is_youtube'] = $is_youtube;
      $variables['is_vimeo'] = $is_vimeo;
      $variables['video_url'] = $background_video_media_value;

      // Use the vid to create a unique ID for use in the JS and add it to
      // drupalSettings.
      $vid = $background_video_media_item->get('vid')->first()->getValue()['value'];
      $id = Html::getUniqueId('v' . $vid);
      $variables['video_id'] = $id;

      // Add the relevant JS.
      if ($is_vimeo) {
        $video_id = array();
        preg_match('/\d+$/', $background_video_media_value, $video_id);
        $variables['#attached']['drupalSettings']['vimeoVideos'][] = array($id => array(
          'videoId' => implode($video_id),
          'width' => $width,
          'height' => $height
        ));
        $variables['#attached']['library'][] = 'wildlife_trust/headline-hero-vimeo';
      }
      else {
        $video_id = array();
        preg_match('/[^=]+$/', $background_video_media_value, $video_id);
        $variables['#attached']['drupalSettings']['youTubeVideos'][] = array($id => array(
          'videoId' => implode($video_id),
          'width' => $width,
          'height' => $height
        ));
        $variables['#attached']['library'][] = 'wildlife_trust/headline-hero-youtube';
      }
    }
  }

  // Check if there's a search so that the CTA button can be hidden.
  $variables['has_search'] = !empty($paragraph->get('field_headline_hero_search')->first())
    ? TRUE
    : FALSE;
}
