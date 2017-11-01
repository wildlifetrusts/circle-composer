<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\Plugin\Field\FieldFormatter\LatLonFormatter;

/**
 * Plugin implementation of the 'geofield_dms' formatter.
 *
 * @FieldFormatter(
 *   id = "geofield_latlon_single",
 *   label = @Translation("Lat/Lon Single"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class LatLonSingleFormatter extends LatLonFormatter {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['single_value'] = 'lat';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['output_format']['#disabled'] = TRUE;

    $elements['single_value'] = [
      '#title' => $this->t('Value'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('single_value'),
      '#options' => $this->valueOptions(),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $output = ['#markup' => ''];
      $geom = $this->geophp->load($item->value);
      if ($geom && $geom->getGeomType() == 'Point') {
        if ($this->getSetting('output_format') == 'decimal') {

          $output = [
            '#type' => 'markup',
            '#markup' => ($this->getSetting('single_value') == 'lat') ? $geom->y() : $geom->x(),
          ];
        }
      }
      $elements[$delta] = $output;
    }

    return $elements;
  }

  /**
   * Helper function to get the formatter settings options.
   *
   * @return array
   *  The formatter settings options.
   */
  protected function valueOptions() {
    return [
      'lat' => $this->t('Latitude'),
      'lng' => $this->t('Longitude'),
    ];
  }

}
