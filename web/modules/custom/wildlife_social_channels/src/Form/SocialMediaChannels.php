<?php

namespace Drupal\wildlife_social_channels\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_media_links\SocialMediaLinksPlatformManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SocialMediaChannels.
 *
 * @package Drupal\wildlife_social_channels\Form
 */
class SocialMediaChannels extends ConfigFormBase {

  protected $platformManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, SocialMediaLinksPlatformManager $platform_manager) {
    parent::__construct($config_factory);

    $this->platformManager = $platform_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.social_media_links.platform')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wildlife_social_media_channels_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['block.block.social_media_links'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $theme = '') {
    $config = $this->config('block.block.social_media_links')->get('settings');

    // A duplicate of the Platforms section of the SocialMediaLinksBlock form.
    $form['platforms'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Platform'),
        $this->t('Platform URL'),
        $this->t('Description'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'platform-order-weight',
        ],
      ],
    ];

    // Keep a note of the highest weight.
    $max_weight = 10;
    $platforms = $this->platformManager->getPlatformsSortedByWeight($config);
    foreach ($platforms as $platform_id => $platform) {
      $form['platforms'][$platform_id]['#attributes']['class'][] = 'draggable';
      $form['platforms'][$platform_id]['#weight'] = $platform['weight'];
      if ($platform['weight'] > $max_weight) {
        $max_weight = $platform['weight'];
      }

      $form['platforms'][$platform_id]['label'] = [
        '#markup' => '<strong>' . $platform['name']->render() . '</strong>',
      ];

      $form['platforms'][$platform_id]['value'] = [
        '#type' => 'textfield',
        '#title' => $platform['name']->render(),
        '#title_display' => 'invisible',
        '#size' => 40,
        '#default_value' => isset($config['platforms'][$platform_id]['value']) ? $config['platforms'][$platform_id]['value'] : '',
        '#field_prefix' => $platform['instance']->getUrlPrefix(),
        '#field_suffix' => $platform['instance']->getUrlSuffix(),
        '#element_validate' => [[get_class($platform['instance']), 'validateValue']],
      ];
      if (!empty($platform['instance']->getFieldDescription())) {
        $form['platforms'][$platform_id]['value']['#description'] = $platform['instance']->getFieldDescription();
      }
      $form['platforms'][$platform_id]['description'] = [
        '#type' => 'textfield',
        '#title' => $platform['name']->render(),
        '#title_display' => 'invisible',
        '#description' => $this->t('The description is used for the title and WAI-ARIA attribute.'),
        '#size' => 40,
        '#placeholder' => $this->t('Find / Follow us on %platform', ['%platform' => $platform['name']->render()]),
        '#default_value' => isset($config['platforms'][$platform_id]['description']) ? $config['platforms'][$platform_id]['description'] : '',
      ];

      $form['platforms'][$platform_id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $platform['name']->render()]),
        '#title_display' => 'invisible',
        '#default_value' => $platform['weight'],
        '#attributes' => ['class' => ['platform-order-weight']],
        // Delta: We need to use the max weight + number of platforms,
        // because if they get re-ordered it could start the count again from
        // the max weight, when the last item is dragged to be the first item.
        '#delta' => $max_weight + count($platforms),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $block_config = $this->config('block.block.social_media_links');
    $block_config->set('settings.platforms', $form_state->getValue('platforms'));
    $block_config->save();

    parent::submitForm($form, $form_state);
  }
}
