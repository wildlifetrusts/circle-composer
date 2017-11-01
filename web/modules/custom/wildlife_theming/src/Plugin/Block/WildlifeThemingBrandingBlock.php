<?php

namespace Drupal\wildlife_theming\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeSettings;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'Site branding' elements.
 *
 * @Block(
 *   id = "wildlife_theming_branding_block",
 *   admin_label = @Translation("Wildlife Trust branding")
 * )
 */
class WildlifeThemingBrandingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a WildlifeThemingBrandingBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'use_site_header_logo' => TRUE,
      'use_site_footer_logo' => TRUE,
      'use_site_strapline' => TRUE,
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Get the theme.
    $theme = $form_state->get('block_theme');

    // Get permissions.
    $url_theme_variation_settings = new Url('wildlife_theming.admin');
    $theme_variation_access = $url_theme_variation_settings->access();
    $theme_variation_url = $url_theme_variation_settings->toString();

    if ($theme_variation_access) {
      // Provide links to the Theme Variation page if the user has access.
      $site_logo_description = $this->t('Defined on the <a href=":variation">Theme Variation</a> page.', [':variation' => $theme_variation_url]);
      $site_strapline_description = $this->t('Defined on the <a href=":variation">Theme Variation</a> page. If the "Display strapline" field is un-checked, the Strapline will not display.', [':variation' => $theme_variation_url]);
    }
    else {
      // Explain that the user does not have access to the Appearance and Theme
      // Settings pages.
      $site_logo_description = $this->t('Defined on the Appearance or Theme Settings page. You do not have the appropriate permissions to change the site logo.');

      // Explain that the user does not have access to the Theme Variation page.
      $site_strapline_description = $this->t('Defined on the Theme Variation page. You do not have the appropriate permissions to change the site logo.');
    }

    $form['block_branding'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Toggle branding elements'),
      '#description' => $this->t('Choose which branding elements you want to show in this block instance.'),
    ];

    $form['block_branding']['use_site_header_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Site header logo'),
      '#description' => $site_logo_description,
      '#default_value' => $this->configuration['use_site_header_logo'],
    ];

    $form['block_branding']['use_site_footer_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Site footer logo'),
      '#description' => $site_logo_description,
      '#default_value' => $this->configuration['use_site_footer_logo'],
    ];

    $form['block_branding']['use_site_strapline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Strap line'),
      '#description' => $site_strapline_description,
      '#default_value' => $this->configuration['use_site_strapline'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $block_branding = $form_state->getValue('block_branding');
    $this->configuration['use_site_header_logo'] = $block_branding['use_site_header_logo'];
    $this->configuration['use_site_footer_logo'] = $block_branding['use_site_footer_logo'];
    $this->configuration['use_site_strapline'] = $block_branding['use_site_strapline'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['site_header_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
      '#alt' => $this->t('Home'),
      '#access' => filter_var($this->configuration['use_site_header_logo'], FILTER_VALIDATE_BOOLEAN),
    ];

    $footer_logo = theme_get_setting('footer_logo.path');

    if (empty($footer_logo)) {
      $themes = \Drupal::service('theme_handler')->listInfo();
      if (isset($themes['wildlife_trust'])) {
        $theme_object = $themes['wildlife_trust'];
        $footer_logo = $theme_object->getPath() . '/logo.svg';
      }
    }

    $build['site_footer_logo'] = [
      '#theme' => 'image',
      '#uri' => file_url_transform_relative(file_create_url($footer_logo)),
      '#alt' => $this->t('Home'),
      '#access' => filter_var($this->configuration['use_site_footer_logo'], FILTER_VALIDATE_BOOLEAN),
    ];

    $strapline = theme_get_setting('strapline');
    if (filter_var($this->configuration['use_site_strapline'], FILTER_VALIDATE_BOOLEAN) && isset($strapline['text'])) {
      $build['site_strapline'] = [
        '#markup' => nl2br($strapline['text']),
        '#access' => filter_var($strapline['show'], FILTER_VALIDATE_BOOLEAN),
      ];

      $build['site_strapline_size'] = $strapline['size'];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      ['theme_variation']
    );
  }

}
