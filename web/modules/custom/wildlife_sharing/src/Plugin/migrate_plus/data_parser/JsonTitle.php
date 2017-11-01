<?php

namespace Drupal\wildlife_sharing\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "wildlife_sharing_json_title",
 *   title = @Translation("JSON Title")
 * )
 */
class JsonTitle extends Json {

  /**
   * Array of arrays. Leaf arrays have uri and title keys.
   * @var array
   */
  protected $urlTitles = [];

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->urlTitles = $configuration['urlTitles'];
  }

  /**
   * {@inheritdoc}
   */
  function getSourceData($url, $item_selector) {
    // Do not get data from the current site.
    if (strpos($url, \Drupal::request()->getHost())) {
      return [];
    }

    try {
      $source_data = parent::getSourceData($url, $item_selector);
    }
    catch (\Exception $exception) {
      return [];
    }
    return !empty($source_data) ? $source_data : [];
  }

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $current = $this->iterator->current();
    if ($current) {
      foreach ($this->fieldSelectors() as $field_name => $selector) {
        $field_data = $current;
        $field_selectors = explode('/', trim($selector, '/'));
        foreach ($field_selectors as $field_selector) {
          $field_data = $field_data[$field_selector];
        }

        $this->currentItem[$field_name] = $field_data;
      }
      // This line is the only change from the parent class. It adds the
      // URL for the current record to the item source.
      $this->currentItem['active_url'] = $this->urlTitles[$this->activeUrl];
      if (!empty($this->configuration['include_raw_data'])) {
        $this->currentItem['raw'] = $current;
      }
      $this->iterator->next();
    }
  }
}
