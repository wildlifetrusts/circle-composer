<?php

namespace Drupal\wildlife_featured_cards;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class DynamicCards.
 *
 * @package Drupal\wildlife_featured_cards
 */
class DynamicCards {

  /**
   * The active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entityTypeManager) {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the content types to filter the featured cards by.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *
   * @return array
   *   An array of node machine names.
   */
  protected function getAllowedContentTypes($paragraph) {
    $allowed_content_types = [
      'blog',
      'event',
      'news',
      'reserve',
    ];

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $content_types_field */
    $content_types_field = $paragraph->get('field_cards_dynamic_type');

    if (!$content_types_field->isEmpty()) {
      $content_types_value = $content_types_field->getValue();
      $allowed_content_types = array_column($content_types_value, 'target_id');
    }

    return $allowed_content_types;
  }

  /**
   * Get the taxonomy vocabularies to filter the featured cards by.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *
   * @return array
   *   An array of vocabulary machine names.
   */
  protected function getAllowedVocabularies($paragraph) {
    $vocabularies_field = $paragraph->get('field_cards_dynamic_vocabulary');
    $allowed_vocabularies = [];

    if (!$vocabularies_field->isEmpty()) {
      $vocabularies_value = $vocabularies_field->getValue();
      $allowed_vocabularies = array_column($vocabularies_value, 'target_id');
    }

    return $allowed_vocabularies;
  }

  /**
   * Get the taxonomy terms to filter the featured cards by.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *
   * @return array
   *   An array of taxonomy term IDs.
   */
  protected function getAllowedTerms($paragraph) {
    $terms_field = $paragraph->get('field_cards_dynamic_term');
    $allowed_terms = [];

    if (!$terms_field->isEmpty()) {
      $terms_value = $terms_field->getValue();
      $allowed_terms = array_column($terms_value, 'target_id');
    }

    return $allowed_terms;
  }

  /**
   * Add date joins to the query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   * @param $content_types
   *   An array of content types to get date values for.
   */
  protected function setDateValues(&$query, $content_types) {
    $dates = [
      'blog' => 'field_blog_publication_date',
      'event' => 'field_event_date',
      'news' => 'field_news_publication_date',
    ];

    $date_values = [];

    foreach ($content_types as $type) {
      if (isset($dates[$type])) {
        $date_value = $dates[$type] . '_value';
        $date_values[] = $date_value;
        $query->leftJoin('node__' . $dates[$type], 'date_' . $type, 'node.nid = date_' . $type . '.entity_id');
        $query->fields('date_' . $type, [$date_value]);
      }
    }

    $date_values[] = 'DATE_FORMAT(FROM_UNIXTIME(node.changed), \'%Y-%m-%d\')';
    $coalesce_fields = implode(',', $date_values);
    $query->addExpression('COALESCE(' . $coalesce_fields . ')', 'sort_date');
  }

  /**
   * Return the count of published Reserves.
   *
   * @return array
   */
  public function getCards(Paragraph $paragraph) {
    $cards = [];
    $content_types = $this->getAllowedContentTypes($paragraph);

    // Set up initial query to retrieve published nodes filtered by allowed
    // content types.
    $query = $this->database->select('node_field_data', 'node');
    $query->leftJoin('node__field_external_link', 'link', 'node.nid = link.entity_id');
    $query->fields('node', ['nid']);
    $query->condition('node.status', NodeInterface::PUBLISHED);
    $query->condition('node.type', $content_types, 'IN');
    $query->isNull('link.entity_id');

    // Apply date fields for sorting.
    $this->setDateValues($query, $content_types);

    // Check for Vocabularies.
    $vocabularies = $this->getAllowedVocabularies($paragraph);

    if (!empty($vocabularies)) {
      $query->join('taxonomy_index', 'ti', 'node.nid = ti.nid');
      $query->join('taxonomy_term_data', 'ttd', 'ti.tid = ttd.tid');
      $query->condition('ttd.vid', $vocabularies, 'IN');
    }

    // Check for Terms.
    $terms = $this->getAllowedTerms($paragraph);

    if (!empty($terms)) {
      $query->join('taxonomy_index', 'ti_2', 'node.nid = ti_2.nid');
      $query->condition('ti_2.tid', $terms, 'IN');
    }

    // Check for the parent entity (Preview nodes do not have this yet so this
    // step can be skipped).
    if ($paragraph->getParentEntity()) {
      $parent_id = $paragraph->getParentEntity()->id();
      // Exclude the paragraph's parent ID so that we don't include the node
      // itself as a recommendation.
      if (!empty($parent_id)) {
        $query->condition('node.nid', $parent_id, '!=');
      }
    }

    // Get results, set limit, order results, and convert to an array of nids.
    $query->orderBy('sort_date', 'DESC');
    $query->distinct();
    $limit = $paragraph->get('field_cards_number')->getString();
    $query->range(0, $limit);
    $result = $query->execute()->fetchAllAssoc('nid');
    $nids = array_keys($result);

    if (!empty($nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      $card_list_items = [];
      $view_builder = $this->entityTypeManager->getViewBuilder('node');

      foreach ($nodes as $node) {
        $node_view = $view_builder->view($node, 'card', $node->language()->getId());
        $card_list_items[] = [
          '#markup' => render($node_view),
          '#wrapper_attributes' => ['class' => ['field__item']],
        ];
      }

      $cards = [
        '#theme' => 'item_list',
        '#items' => $card_list_items,
        '#attributes' => ['class' => ['featured-cards__dynamic-items']],
      ];
    }

    return $cards;
  }
}
