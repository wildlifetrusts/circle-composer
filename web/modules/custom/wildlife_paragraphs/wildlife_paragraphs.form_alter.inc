<?php
/**
 * @file
 * Include file for Wildlife Paragraphs form alters.
 */

/**
 * Helper function to turn a field's parents in to an array for use with States.
 *
 * @param $field_parents
 *   An array of a field's parents.
 *
 * @return string
 *   A string version of that array for use with States js.
 */
function _wildlife_paragraphs_get_paragraph_name_from_parents($field_parents) {
  $input_name = '';

  if (is_array($field_parents)) {
    foreach ($field_parents as $key => $field_parent_name) {
      if ($key === 0) {
        $input_name .= $field_parent_name;
      } else {
        $input_name .= '[' . $field_parent_name . ']';
      }
    }
  }

  return $input_name;
}

/**
 * Remove the text format helpers on a field that's being hidden.
 *
 * #after_build callback.
 */
function _wildlife_paragraphs_allowed_formats_remove_textarea_help($form_element, \Drupal\Core\Form\FormStateInterface $form_state) {
  if (isset($form_element['format'])) {
    // All this stuff is needed to hide the help text.
    unset($form_element['format']['guidelines']);
    unset($form_element['format']['help']);
    unset($form_element['format']['#type']);
    unset($form_element['format']['#theme_wrappers']);
  }

  return $form_element;
}

/**
 * Function triggered via wildlife_paragraphs_field_widget_form_alter() to alter
 * the Quick Links paragraph elements.
 */
function _wildlife_paragraphs_quick_links_widget_form_alterations(&$element, &$form_state, $context, $field_parents, $field_name) {
  // Some quick links fields should only appear when being used as a node
  // component. When used inside Flexible blocks they should be hidden.
  $quick_links_component_only_fields = [
    'field_quick_links_extra_wide',
    'field_quick_links_title',
  ];

  if (is_array($field_parents) && in_array($field_name, $quick_links_component_only_fields)) {
    foreach ($field_parents as $field_parent) {
      if (strpos($field_parent, 'field_flexible_block_content_') === 0) {
        $element['#access'] = FALSE;
      }
    }
  }
}

/**
 * Function triggered via wildlife_paragraphs_field_widget_form_alter() to alter
 * the Image and Video paragraph elements.
 */
function _wildlife_paragraphs_image_video_widget_form_alterations(&$element, &$form_state, $context, $field_parents, $field_name, \Drupal\Core\Field\FieldItemList $items, \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $parent) {
  $parent_entity = $parent->getValue();
  $parent_id = $parent_entity->getEntityType()->id();
  $item_parent_bundle = $items->getParent()->getValue()->bundle();

  if ($parent_id == 'paragraph' && in_array($item_parent_bundle, ['audio', 'image', 'video'])) {
    // Image and Video add states to accompanying text fields.
    $accompanying_text_fields = [
      'field_' . $item_parent_bundle . '_rich_text',
      'field_' . $item_parent_bundle . '_position',
    ];

    if (is_array($field_parents) && in_array($field_name, $accompanying_text_fields)) {
      if ($context['widget'] instanceof \Drupal\text\Plugin\Field\FieldWidget\TextareaWidget) {
        $element['#after_build'][] = '_wildlife_paragraphs_allowed_formats_remove_textarea_help';
      }

      $input_name = _wildlife_paragraphs_get_paragraph_name_from_parents($field_parents);
      $input_name .= '[field_' . $item_parent_bundle . '_accompanying_text][value]';

      $element['#states'] = array(
        'visible' => array(
          ':input[name="' . $input_name . '"]' => array('checked' => TRUE),
        ),
      );
    }

    $extra_wide_field = 'field_' . $item_parent_bundle . '_extra_wide';
    $accordion_field = 'field_accordion_accordion_item';

    if (is_array($field_parents) && ($field_name == $extra_wide_field) && in_array($accordion_field, $field_parents, TRUE)) {
      // Disable the field and add a useful comment instead of just hiding it.
      $element['#disabled'] = TRUE;
      $element['#suffix'] = t('You cannot set content to be extra wide when it is appearing inside an Accordion.');
    }
  }
}

/**
 * Function triggered via wildlife_paragraphs_field_widget_form_alter() to alter
 * the Spotlight paragraph element.
 */
function _wildlife_paragraphs_spotlight_widget_form_alterations(&$element, &$form_state, $context, $field_parents, $field_name) {
  $input_name = _wildlife_paragraphs_get_paragraph_name_from_parents($field_parents);

  // Link Appearance field states.
  if (is_array($field_parents) && $field_name == 'field_spotlight_link_appearance') {
    $element['#states'] = array(
      'visible' => array(
        ':input[name="' . $input_name . '[field_spotlight_link][0][uri]"]' => array('!value' => ''),
      ),
    );
  }

  // Button type field states.
  if (is_array($field_parents) && $field_name == 'field_spotlight_button_type') {
    $element['#states'] = array(
      'visible' => array(
        ':input[name="' . $input_name . '[field_spotlight_link][0][uri]"]' => array('!value' => ''),
        ':input[name="' . $input_name . '[field_spotlight_link_appearance]"]' => array('!value' => 'block'),
      ),
    );
  }

  // Image type field states.
  if (is_array($field_parents) && $field_name == 'field_spotlight_image_type') {
    $element['#states'] = array(
      'visible' => array(
        ':input[name="' . $input_name . '[field_spotlight_display_type]"]' => array('!value' => 'separate'),
      ),
    );
  }

  // Image field states.
  if (is_array($field_parents) && $field_name == 'field_spotlight_image') {
    $element['#states'] = [
      'visible' => [
        [':input[name="' . $input_name . '[field_spotlight_image_type]"]' => ['!value' => 'silhouette']],
        'or',
        [':input[name="' . $input_name . '[field_spotlight_display_type]"]' => ['value' => 'separate']],
      ],
    ];
  }

  // Silhouette field states.
  if (is_array($field_parents) && $field_name == 'field_spotlight_silhouette') {
    $element['#states'] = [
      'visible' => [
        [':input[name="' . $input_name . '[field_spotlight_image_type]"]' => ['value' => 'silhouette']],
      ],
    ];
  }
}

/**
 * Function triggered via wildlife_paragraphs_field_widget_form_alter() to alter
 * the Statistics paragraph element.
 */
function _wildlife_paragraphs_statistics_widget_form_alterations(&$element, &$form_state, $context, $field_parents, $field_name) {
  if (!is_array($field_parents)) {
    $field_parents = $context['form']['#parents'];
  }
  $input_name = _wildlife_paragraphs_get_paragraph_name_from_parents($field_parents);

  // Link Appearance field states.
  if (is_array($field_parents)) {
    if (strpos($field_name, 'field_statistic_dynamic_') === 0) {
      $element['#states']['visible'][':input[name="' . $input_name . '[field_statistic_type]"]'] = array('value' => 'dynamic');
    }

    if ($field_name == 'field_statistic_static_type') {
      $element['#states']['visible'][':input[name="' . $input_name . '[field_statistic_type]"]'] = array('value' => 'static');
      $element['#states']['required'][':input[name="' . $input_name . '[field_statistic_type]"]'] = array('value' => 'static');
    }

    if ($field_name == 'field_statistic_static_number') {
      $element['value']['#states']['visible'][':input[name="' . $input_name . '[field_statistic_type]"]'] = array('value' => 'static');
    }

    if ($field_name == 'field_statistic_static_custom') {
      $element['value']['#states'] = array(
        'visible' => [
          ':input[name="' . $input_name . '[field_statistic_type]"]' => array('!value' => 'dynamic'),
        ],
      );
    }
  }
}

/**
 * Function triggered via wildlife_paragraphs_field_widget_form_alter() to
 * show and/or hide image, silhouette and call to action fields on headline hero
 * paragraphs based on which options have been selected.
 */
function _wildlife_paragraphs_headline_hero_form_alterations(&$element, &$form_state, $context, $field_parents, $field_name) {
  $input_name = _wildlife_paragraphs_get_paragraph_name_from_parents($field_parents);

  // Background image visibility settings.
  if (is_array($field_parents) && $field_name == 'field_headline_hero_image') {
    $element['#states'] = [
      'visible' => [
        [':input[name="' . $input_name . '[field_headline_hero_background]"]' => ['value' => 'image']],
      ],
    ];
  }

  // Silhouette background visibility settings.
  if (is_array($field_parents) && $field_name == 'field_headline_hero_silhouette') {
    $element['#states'] = [
      'visible' => [
        [':input[name="' . $input_name . '[field_headline_hero_background]"]' => ['value' => 'silhouette']],
      ],
    ];
  }

  // Call to action visibility settings.
  if (is_array($field_parents) && $field_name == 'field_headline_hero_cta') {
    $element['#states'] = [
      'visible' => [
        [':input[name="' . $input_name . '[field_headline_hero_search]"]' => ['value' => '_none']],
      ],
    ];
  }
}
