<?php

namespace Drupal\wildlife_admin\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget;

/**
 * Plugin implementation of the 'tweaked_entity_reference paragraphs' widget.
 *
 * We hide add / remove buttons when translating to avoid accidental loss of
 * data because these actions effect all languages.
 *
 * @FieldWidget(
 *   id = "tweaked_entity_reference_paragraphs",
 *   label = @Translation("Paragraphs Classic (Tweaked)"),
 *   description = @Translation("A paragraphs inline form widget."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class TweakedInlineParagraphsWidget extends InlineParagraphsWidget {
  /**
   * @inheritdoc
   */
  protected function getAccessibleOptions() {
    $this->accessOptions = parent::getAccessibleOptions();

    if ($this->accessOptions !== NULL && !isset($this->accessOptions['_none'])) {
      $empty_option = ['_none' => $this->t('- Choose a component -')];

      return $empty_option + $this->accessOptions;
    }
  }
  /**
   * Builds list of actions based on paragraphs type.
   *
   * @return array
   *   The form element array.
   */
  protected function buildSelectAddMode() {
    $add_more_elements = parent::buildSelectAddMode();

    // Add the Select element to a wrapper.
    $add_more_select = $add_more_elements['add_more_select'];

    $add_more_elements['paragraphs_add_wrapper'] = [
      '#type' => 'container',
      '#tree' => FALSE,
      '#attributes' => ['class' => ['form__paragraphs-add']],
      'add_more_select' => $add_more_select,
    ];

    unset ($add_more_elements['add_more_select']);

    // Add the button to it's own wrapper and add that to the above wrapper.
    $add_more_button = $add_more_elements['add_more_button'];

    $add_more_button['#submit'] = [[get_class($this), 'addMoreSubmit']];
    $add_more_button['#ajax']['callback'] = [get_class($this), 'addMoreAjax'];

    $add_more_elements['paragraphs_add_wrapper']['actions'] = [
      '#type' => 'container',
      '#tree' => FALSE,
      '#attributes' => ['class' => ['paragraphs-add__action']],
      'add_more_button' => $add_more_button,
    ];

    unset ($add_more_elements['add_more_button']);

    // Return the updated form.
    return $add_more_elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));

    // Add a DIV around the delta receiving the Ajax effect.
    $delta = $element['#max_delta'];
    $element[$delta]['#prefix'] = '<div class="ajax-new-content">' . (isset($element[$delta]['#prefix']) ? $element[$delta]['#prefix'] : '');
    $element[$delta]['#suffix'] = (isset($element[$delta]['#suffix']) ? $element[$delta]['#suffix'] : '') . '</div>';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    // Increment the items count.
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    if ($widget_state['real_item_count'] < $element['#cardinality'] || $element['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $widget_state['items_count']++;
    }

    if (isset($button['#bundle_machine_name'])) {
      $widget_state['selected_bundle'] = $button['#bundle_machine_name'];
    }
    else {
      $selected_value = $element['add_more']['paragraphs_add_wrapper']['add_more_select']['#value'];
      if ($selected_value != '_none') {
        $widget_state['selected_bundle'] = $element['add_more']['paragraphs_add_wrapper']['add_more_select']['#value'];
      }
    }

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }

}
