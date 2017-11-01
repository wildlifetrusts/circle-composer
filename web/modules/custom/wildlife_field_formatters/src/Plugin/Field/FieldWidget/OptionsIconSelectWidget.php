<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'options_icon_select' widget.
 *
 * @FieldWidget(
 *   id = "options_icon_select",
 *   label = @Translation("Icon select list"),
 *   field_types = {
 *     "list_string"
 *   },
 *   multiple_values = TRUE
 * )
 */
class OptionsIconSelectWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#field_prefix'] = '<div class="icon-switcher"></div>';
    $element['#attached']['library'][] = 'wildlife_trust/admin-icon-picker';

    return $element;
  }
}
