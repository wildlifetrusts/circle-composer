<?php

namespace Drupal\wildlife_search\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a hero search form element.
 *
 * @RenderElement("hero_search_form")
 */
class HeroSearchForm extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $form = [
      '#theme' => 'hero_search_form',
      '#search_type' => '',
      '#identifier' => '',
      '#pre_render' => [
        [static::class, 'preRenderHeroSearchForm'],
      ],
      '#cache' => [
        'contexts' => [
          'url.query_args',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Pre-render callback: Renders a generic HTML tag with attributes into #markup.
   *
   * @param array $element
   *   An associative array containing:
   *   - #search_type: The search type key.
   *
   * @return array
   */
  public static function preRenderHeroSearchForm($element) {
    $search_type = $element['#search_type'];

    $placeholders = [
      'location' => t('Enter Postcode, Town or Place'),
      'keyword' => t('Enter Search Term'),
      'species' => t('Enter Species Name'),
      'habitat' => t('Enter Habitat Name'),
    ];

    $search_types = [
      'global' => [
        'identifier' => 'search',
        'placeholder' => 'keyword',
        'form_action_url' => '/search',
      ],
      'global_site_search' => [
        'identifier' => 'search',
        'placeholder' => 'keyword',
        'form_action_url' => '/search',
      ],
      'trusts' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/wildlife-trusts',
      ],
      'reserves' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/nature-reserves',
      ],
      'species' => [
        'identifier' => 'search',
        'placeholder' => 'species',
        'form_action_url' => '/wildlife-explorer/search',
      ],
      'habitat' => [
        'identifier' => 'search',
        'placeholder' => 'habitat',
        'form_action_url' => '/habitats/search',
      ],
      'event' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/events',
      ],
      'events' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/events',
      ],
      'event_reserve' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/explore-near-me',
      ],
      'jobs' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/jobs',
      ],
      'volunteering_opportunities' => [
        'identifier' => 'location',
        'placeholder' => 'location',
        'form_action_url' => '/volunteering-opportunities',
      ],
    ];

    if (isset($search_types[$search_type])) {
      $search_values = $search_types[$search_type];
      $element['#placeholder'] = isset($placeholders[$search_values['placeholder']]) ? $placeholders[$search_values['placeholder']] : '';

      // Get the currently selected language to construct the URL.
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $element['#action_url'] = $language == 'en' ? $search_values['form_action_url'] : '/' . $language . $search_values['form_action_url'];
      $element['#search_type'] = $search_type;

      $current_path = \Drupal::service('path.current')->getPath();
      $path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);

      if (isset($element['#identifier']) && $element['#identifier'] == '') {
        $element['#identifier'] = $search_values['identifier'];
      }

      $identifier = \Drupal::request()->get($element['#identifier']);
      if ($path_alias == $search_values['form_action_url'] && $identifier) {
        $element['#value'] = $identifier;
      }
    }

    return $element;
  }

}
