<?php

namespace Drupal\wildlife_theming\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\system\Form\ThemeSettingsForm;
use Drupal\wildlife_theming\ThemeVariation;

/**
 * Class ThemeVariationAdmin.
 *
 * @package Drupal\wildlife_theming\Form
 */
class ThemeVariationAdmin extends ThemeSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wildlife_theming_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return $this->editableConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $theme = '') {
    $container = \Drupal::getContainer();

    if (!$theme) {
      $theme = ThemeVariation::create($container)->getDefaultTheme();
    }

    $form = parent::buildForm($form, $form_state, $theme);

    // Footer logo fields.
    $responsive_favicons_page = Link::createFromRoute('Responsive favicons page', 'responsive_favicons.admin')->toString();

    $form['logos'] = array(
      '#type' => 'details',
      '#title' => t('Logos'),
      '#open' => TRUE,
      '#description' => $this->t('Alter the site\'s header and footer logos. To update the favicons or touchicons visit the @page.', ['@page' => $responsive_favicons_page]),
      '#tree' => FALSE,
    );

    $this->processFormForThemeVariation($form);

    $form['logos']['footer_logo'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer logo'),
      '#open' => TRUE,
      '#tree' => FALSE,
    ];

    $form['logos']['footer_logo']['default_footer_logo'] = [
      '#type' => 'checkbox',
      '#title' => t('Use the logo supplied by the theme'),
      '#default_value' => theme_get_setting('footer_logo.use_default', $theme),
    ];

    $form['logos']['footer_logo']['settings'] = [
      '#type' => 'container',
      '#states' => [
        // Hide the logo settings when using the default logo.
        'invisible' => [
          'input[name="default_footer_logo"]' => ['checked' => TRUE],
        ],
      ],
      '#tree' => FALSE,
    ];

    $form['logos']['footer_logo']['settings']['footer_logo_path'] = [
      '#type' => 'textfield',
      '#title' => t('Path to custom logo'),
      '#default_value' => theme_get_setting('footer_logo.path', $theme),
    ];

    $form['logos']['footer_logo']['settings']['footer_logo_upload'] = [
      '#type' => 'file',
      '#title' => t('Upload logo image'),
      '#description' => t("If you don't have direct file access to the server, use this field to upload your logo.")
    ];

    $this->addLogoUploadDescription($form['logos']['footer_logo']['settings']['footer_logo_upload']);

    // Strapline fields.
    $form['strapline'] = [
      '#type' => 'details',
      '#title' => $this->t('Strapline'),
      '#open' => TRUE,
      '#description' => $this->t('Please note that you must test the strapline across all device widths to check that the strapline text and size you have chosen will work together for all viewports. You can do this by opening the site in a browser and then manually resizing the width of the browser window.'),
      '#tree' => TRUE,
    ];

    $form['strapline']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display strapline'),
      '#description' => $this->t('Un-check this box if you do not wish to show the strapline on the site front end.'),
      '#default_value' => theme_get_setting('strapline.show', $theme),
    ];

    $theme_text = theme_get_setting('strapline.text', $theme);
    $site_name = \Drupal::config('system.site')->get('name');

    $form['strapline']['text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Strapline text'),
      '#rows' => 3,
      '#cols' => 50,
      '#resizable' => 'none',
      '#default_value' => empty($theme_text) ? $site_name : $theme_text,
    ];

    $form['strapline']['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Strapline size'),
      '#options' => [
        'small' => t('Small'),
        'medium' => t('Medium'),
        'large' => t('Large'),
      ],
      '#default_value' => theme_get_setting('strapline.size', $theme),
    ];

    // Colour scheme fields.
    $form['colours'] = array(
      '#type' => 'details',
      '#title' => t('Colour scheme'),
      '#open' => TRUE,
    );

    ThemeVariation::create($container)->createColourSchemesFormItem($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($this->moduleHandler->moduleExists('file')) {
      // Handle file uploads.
      $validators = array('file_validate_is_image' => array());

      // Check for a new uploaded footer logo.
      $file = file_save_upload('footer_logo_upload', $validators, FALSE, 0);
      if (isset($file)) {
        // File upload was attempted.
        if ($file) {
          // Put the temporary file in form_values so we can save it on submit.
          $form_state->setValue('footer_logo_upload', $file);
        }
        else {
          // File upload failed.
          $form_state->setErrorByName('footer_logo_upload', $this->t('The footer logo could not be uploaded.'));
        }
      }

      $validators = array('file_validate_extensions' => array('ico png gif jpg jpeg apng svg'));

      // When intending to use the default logo, unset the logo_path.
      if ($form_state->getValue('default_footer_logo')) {
        $form_state->unsetValue('footer_logo_path');
      }

      // If the user provided a path for a footer logo file, make sure a file
      // exists at that path.
      if ($form_state->getValue('footer_logo_path')) {
        $path = $this->validatePath($form_state->getValue('footer_logo_path'));
        if (!$path) {
          $form_state->setErrorByName('footer_logo_path', $this->t('The custom footer logo path is invalid.'));
        }
      }
    }

    // Strapline validation.
    if (!empty($form_state->getValue('strapline'))) {
      $strapline_values = $form_state->getValue('strapline');

      if (isset($strapline_values['text'])) {
        $linebreaks = substr_count($strapline_values['text'], "\n");

        if ($linebreaks > 2) {
          $form_state->setErrorByName('strapline][text', $this->t('The strapline may not span more than 3 lines.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // If the user uploaded a new footer logo, save it to a permanent location
    // and use it in place of the default theme-provided file.
    if (!empty($values['footer_logo_upload'])) {
      $filename = file_unmanaged_copy($values['footer_logo_upload']->getFileUri());
      $values['default_footer_logo'] = 0;
      $values['footer_logo_path'] = $filename;
    }

    // If the user entered a path relative to the system files directory for
    // a footer logo, store a public:// URI so the theme system can handle it.
    if (!empty($values['footer_logo_path'])) {
      $values['footer_logo_path'] = $this->validatePath($values['footer_logo_path']);
    }

    // Set the footer logo value for config storage and unset the values that
    // shouldn't be set (saving them for resetting later).
    $footer_logo = [
      'use_default' => $values['default_footer_logo'],
      'path' => isset($values['footer_logo_path']) ? $values['footer_logo_path'] : '',
    ];

    $form_state->setValue('footer_logo', $footer_logo);
    $saved_values = [
      'default_footer_logo' => $values['default_footer_logo'],
      'footer_logo_path' => isset($values['footer_logo_path']) ? $values['footer_logo_path'] : '',
      'footer_logo_upload' => isset($values['footer_logo_upload']) ? $values['footer_logo_upload'] : '',
    ];

    foreach ($saved_values as $key => $value) {
      $form_state->unsetValue($key);
    }

    // Clear the cache.
    Cache::invalidateTags(['theme_variation']);

    // Submit the form using the parent submission.
    parent::submitForm($form, $form_state);

    // Reset form state items we temporarily hid from parent form submission.
    foreach ($saved_values as $key => $value) {
      $form_state->setValue($key, $value);
    }
  }

  /**
   * Add an additional description about the logo upload.
   *
   * @param $element
   *   A form element array.
   */
  protected function addLogoUploadDescription(&$element) {
    $existing_description = isset($element['#description']) ? $element['#description'] : '';
    $additional_description = $this->t('Uploaded images will not be re-sized in the back-end, so should ideally be no larger than 260 x 130px.');

    $element['#description'] = $existing_description . '<br />' . $additional_description;
  }

  /**
   * Process the Theme Setting Form to update values for Theme Variation form.
   *
   * @param $form
   *  The form array.
   */
  protected function processFormForThemeVariation(&$form) {
    // Update the default logo text.
    $form['logo']['#title'] = t('Header logo');

    // Add a description.
    $this->addLogoUploadDescription($form['logo']['settings']['logo_upload']);

    // Move header logo field to inside Logos fieldset.
    $form['logos']['logo'] = $form['logo'];
    unset($form['logo']);

    // Hide "Page Element Display" and "Favicon".
    $form['theme_settings']['#access'] = $this->currentUser()->hasPermission('administer themes');
    $form['favicon']['#access'] = $this->currentUser()->hasPermission('administer themes');
  }
}
