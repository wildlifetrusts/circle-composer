<?php

namespace Drupal\wildlife_footer_blocks\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\wildlife_footer_blocks\Plugin\Block\WildlifeFooterInformationBlock;

/**
 * Configure Cookie Control block settings.
 */
class WildlifeFooterInformationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wildlife_footer_blocks_footer_information';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['block.block.footer_information'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $block_config = $this->config('block.block.footer_information');
    $default_value = $block_config->get('settings.footer_information');

    $form['footer_information'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Footer information'),
      '#description' => $this->t(WildlifeFooterInformationBlock::getFooterInformationFieldDescription()),
      '#default_value' => $default_value,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('block.block.footer_information')
      ->set('settings.footer_information', $form_state->getValue('footer_information')['value'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
