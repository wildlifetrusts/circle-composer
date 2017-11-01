<?php
/**
 * @file
 * Include file for Wildlife Paragraph preprocess functions.
 */

/**
 * Helper function to preprocess Headline Hero Search fields.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_field().
 *
 * @param $element
 *  An array of the current element.
 */
function _wildlife_paragraphs_preprocess_field_headline_hero_search(&$variables, $element) {
  /** @var \Drupal\Core\Field\FieldItemList $items */
  $items = $element['#items'];

  if (!$items->isEmpty()) {
    // Get the type of search that has been set.
    $search_type = $items->getString();

    $variables['hero_search'] = [
      '#type' => 'hero_search_form',
      '#search_type' => $search_type,
    ];
  }
}

/**
 * Helper function to preprocess Spotlight Title fields.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_field().
 *
 * @param $element
 *  An array of the current element.
 */
function _wildlife_paragraphs_preprocess_field_spotlight_title(&$variables, $element) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $element['#object'];

  // Add a class to the title reflecting the title size set.
  if (!$paragraph->get('field_spotlight_title_size')->isEmpty()) {
    $title_size = $paragraph->get('field_spotlight_title_size')->getString();
    $variables['attributes']['class'][] = 'spotlight__title--' . $title_size;
  }
}

/**
 * Helper function to preprocess Gallery items fields.
 *
 * @param $variables
 *  The $variables array provided by hook_preprocess_field().
 *
 * @param $element
 *  An array of the current element.
 */
function _wildlife_paragraphs_preprocess_field_gallery_items(&$variables, $element) {
  foreach ($variables['items'] as &$field_item) {
    /** @var \Drupal\Core\Template\Attribute $attributes */
    $attributes = &$field_item['attributes'];
    $attributes->setAttribute('aria-label', 'Image gallery slide');
  }
}
