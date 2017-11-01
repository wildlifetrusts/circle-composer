<?php

namespace Drupal\wildlife_theming;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ThemeVariation.
 *
 * @package Drupal\wildlife_theming
 */
class ThemeVariation {
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a Theme Variation object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Retrieve the available colour schemes for the default theme.
   *
   * @param string $theme
   *    The name of the theme.
   *
   * @return array
   *   An array of available colour schemes based on files present in the theme.
   */
  public function getColourSchemes($theme = '') {
    // If no theme is set, use the default theme.
    if (!$theme) {
      $theme = $this->getDefaultTheme();
    }

    // Get the chosen theme and the relevant paths.
    $theme_path = drupal_get_path('theme', $theme);
    $colour_schemes_folder =  $theme_path . '/colour_schemes';
    $colour_schemes = array();

    // Loop through the theme's colour schemes folder if it exists and get the
    // colour schemes from inside, checking against the existence of a
    // corresponding .scss file.
    if (is_dir($colour_schemes_folder)) {
      $colour_scheme_pngs = array_slice(scandir($colour_schemes_folder), 2);

      foreach ($colour_scheme_pngs as $scheme) {
        $scheme_parts = explode('.', $scheme);
        $scheme_colour = $scheme_parts[0];
        $scheme_css = $theme_path . '/css/' . $theme . '.' . $scheme_colour . '.styles.css';

        if (file_exists($scheme_css)) {
          $colour_schemes[$scheme_colour] = ucfirst($scheme_colour);
        }
      }

      // Move the default scheme to the top of the list.
      if (isset($colour_schemes['default'])) {
        $colour_schemes = array('default' => $colour_schemes['default']) + $colour_schemes;
      }
    }

    return $colour_schemes;
  }

  /**
   * Get the default theme of the site.
   *
   * @return string
   *   The machine name of the current default theme.
   */
  public function getDefaultTheme() {
    $theme_config = $this->configFactory->get('system.theme');
    $default_theme = $theme_config->get('default');

    return $default_theme;
  }

  /**
   * Get the colour scheme options form use with a form element.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return array
   *   A keyed array with the scheme label and palette image.
   */
  public function getColourSchemesOptions($theme = '') {
    // If no theme is set, use the default theme.
    if (!$theme) {
      $theme = $this->getDefaultTheme();
    }

    $colour_schemes = $this->getColourSchemes($theme);
    $colour_scheme_options = [];
    $theme_path = drupal_get_path('theme', $theme) . '/css/';

    foreach ($colour_schemes as $key => $label) {
      $css_file = $theme_path . $theme . '.' . $key . '.styles.css';

      if (file_exists($css_file)) {
        $colour_scheme_image = '/' . drupal_get_path('theme', $theme) . '/colour_schemes/' . $key . '.png';
        $palette_image = [
          '#theme' => 'image',
          '#uri' => $colour_scheme_image,
          '#alt' => $label,
        ];

        $colour_scheme_options[$key] = [
          'scheme' => $label,
          'palette' => render($palette_image),
        ];
      }
    }

    return $colour_scheme_options;
  }


  /**
   * Update a form with a colour scheme field.
   *
   * @param $form
   *   The form array.
   *
   * @param string $theme
   *   The machine name of the theme.
   */
  public function createColourSchemesFormItem(&$form, $theme = '') {
    // If no theme is set, use the default theme.
    if (!$theme) {
      $theme = $this->getDefaultTheme();
    }

    $colour_scheme_options = $this->getColourSchemesOptions($theme);

    if (count($colour_scheme_options) < 2) {
      $form['colours']['#description'] = t('There are no colour schemes available for this theme.');
    }
    else {
      $header = [
        'scheme' => t('Scheme'),
        'palette' => t('Palette'),
      ];

      $form['colours']['colour_scheme'] = array(
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $colour_scheme_options,
        '#multiple' => FALSE,
        '#default_value' => theme_get_setting('colour_scheme', $theme),
        '#attributes' => [
          'style' => 'max-width: 300px;',
        ],
      );
    }
  }
}
