<?php

namespace Drupal\wildlife_footer_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Footer Information' block.
 *
 * @Block(
 *   id = "footer_information",
 *   admin_label = @Translation("Footer information")
 * )
 */
class WildlifeFooterInformationBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $saved_settings = \Drupal::config('block.block.footer_information')->get('settings');

    if (!isset($configuration['footer_information'])) {
      $this->configuration['footer_information'] = isset($saved_settings['footer_information']) ? $saved_settings['footer_information'] : '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * Helper to return the description of the field.
   *
   * @return string
   *   The description.
   */
  public static function getFooterInformationFieldDescription() {
    return 'This information text will appear in the footer below the menu.';
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $default_value = isset($this->configuration['footer_information']) ? $this->configuration['footer_information'] : '';

    $form['footer_information'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Footer information'),
      '#description' => $this->t($this->getFooterInformationFieldDescription()),
      '#default_value' => $default_value,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['footer_information'] = $form_state->getValue('footer_information')['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = array(
      '#type' => 'markup',
      '#markup' => t($this->configuration['footer_information']),
    );

    return $build;
  }
}
