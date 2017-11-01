<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'boolean' formatter.
 *
 * @FieldFormatter(
 *   id = "boolean_hide_on_false",
 *   label = @Translation("Boolean (hide on false)"),
 *   field_types = {
 *     "boolean",
 *   }
 * )
 */
class BooleanHideOnFalseFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($item->value) {
        $elements[$delta] = ['#markup' => $this->getFieldSetting('on_label')];
      }
    }

    return $elements;
  }
}
