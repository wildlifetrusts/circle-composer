<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'intelligent_text_trimmed' formatter.
 *
 * @FieldFormatter(
 *   id = "intelligent_text_trimmed",
 *   label = @Translation("Intelligent Trimmed"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "string_long"
 *   },
 *   quickedit = {
 *     "editor" = "form"
 *   }
 * )
 */
class IntelligentTextTrimmedFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'trim_length' => '600',
      'word_boundary' => 1,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['trim_length'] = array(
      '#title' => t('Trimmed limit'),
      '#type' => 'number',
      '#field_suffix' => t('characters'),
      '#default_value' => $this->getSetting('trim_length'),
      '#description' => t('If the summary is not set, the trimmed %label field will end at the last full sentence before this character limit.', array('%label' => $this->fieldDefinition->getLabel())),
      '#min' => 1,
      '#required' => TRUE,
    );

    $element['word_boundary'] = array(
      '#title' => t('Trim on word boundary'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('word_boundary'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $summary[] = t('Trimmed limit: @trim_length characters', array('@trim_length' => $this->getSetting('trim_length')));
    $trim_on_word_boundary = $this->getSetting('word_boundary') ? 'Yes' : 'No';
    $summary[] = t('Trim on word boundary: @word_boundary', array('@word_boundary' => $trim_on_word_boundary));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    $render_as_summary = function (&$element) {
      // Make sure any default #pre_render callbacks are set on the element,
      // because text_pre_render_summary() must run last.
      $element += \Drupal::service('element_info')->getInfo($element['#type']);
      // Add the #pre_render callback that renders the text into a summary.
      $element['#pre_render'][] = [IntelligentTextTrimmedFormatter::class, 'preRenderSummary'];
      // Pass on the trim length and word boundary trim to the #pre_render
      // callback via a property.
      $element['#text_summary_trim_length'] = $this->getSetting('trim_length');
      $element['#trim_on_word_boundary'] = $this->getSetting('word_boundary');
    };

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta] = array(
        '#type' => 'processed_text',
        '#text' => $item->value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      );

      $render_as_summary($elements[$delta]);
    }

    return $elements;
  }

  /**
   * Pre-render callback: Renders a processed text element's #markup as a summary.
   *
   * @param array $element
   *   A structured array with the following key-value pairs:
   *   - #markup: the filtered text (as filtered by filter_pre_render_text())
   *   - #format: containing the machine name of the filter format to be used to
   *     filter the text. Defaults to the fallback format. See
   *     filter_fallback_format().
   *   - #text_summary_trim_length: the desired character length of the summary
   *     (used by text_summary())
   *
   * @return array
   *   The passed-in element with the filtered text in '#markup' trimmed.
   *
   * @see filter_pre_render_text()
   */
  public static function preRenderSummary(array $element) {
    $value = $element['#markup'];
    $max_length = $element['#text_summary_trim_length'];
    $matches = array();

    if (Unicode::strlen($value) > $max_length) {
      $value = Unicode::substr($value, 0, $max_length);
      if ($element['#trim_on_word_boundary']) {
        $regex = "(.*)\b.+";
        if (function_exists('mb_ereg')) {
          mb_regex_encoding('UTF-8');
          $found = mb_ereg($regex, $value, $matches);
        }
        else {
          $found = preg_match("/$regex/us", $value, $matches);
        }
        if ($found) {
          $value = $matches[1];
        }
      }
      // Remove scraps of HTML entities from the end of a strings
      $value = rtrim(preg_replace('/(?:<(?!.+>)|&(?!.+;)).*$/us', '', $value));

      // Add an ellipsis.
      $value .= t('…');
    }

    // Normalize the HTML.
    $value = Html::normalize($value);

    $element['#markup'] = $value;
    return $element;
  }

}

