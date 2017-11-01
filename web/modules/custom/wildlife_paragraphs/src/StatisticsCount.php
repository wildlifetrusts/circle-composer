<?php

namespace Drupal\wildlife_paragraphs;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class StatisticsCount.
 *
 * @package Drupal\wildlife_paragraphs
 */
class StatisticsCount {

  /**
   * The active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The interface for entity storage classes.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch) {
    $this->database = $database;
    $this->routeMatch = $routeMatch;
  }

  /**
   * Returns the current Node type.
   *
   * @return bool
   */
  public function getNodeType() {
    if ($node = $this->routeMatch->getParameter('node')) {
      return $node->bundle();
    }
    elseif ($node = $this->routeMatch->getParameter('node_preview')) {
      return $node->bundle();
    }
    else {
      return FALSE;
    }
  }

  /**
   * Returns the current Node ID.
   *
   * @return int
   */
  public function getNodeId() {
    if ($node = $this->routeMatch->getParameter('node')) {
      return $node->id();
    }
    else {
      return FALSE;
    }
  }

  /**
   * Return the count of published Events whose end date has not yet passed.
   *
   * @return int
   */
  public function getUpcomingEvents() {
    $node_type = $this->getNodeType();
    $today = date('Y-m-d');

    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'event');
    $query->condition('field_event_date.end_value', $today, '>=');

    if ($node_type == 'reserve') {
      $nid = $this->getNodeId();
      $query->condition('field_event_reserve.target_id', $nid);
    }

    $count = $query->count()->execute();

    return $count;
  }

  /**
   * Get the Event count text.
   *
   * @param int $amount
   *   The number of events.
   *
   * @return string
   */
  public function getDynamicStatText($type, $amount) {
    $formatted_amount = number_format($amount);

    if ($type == 'reserves') {
      $text = \Drupal::translation()->formatPlural($formatted_amount, '1 Reserve', '@count Reserves');
    }
    else {
      $text = \Drupal::translation()->formatPlural($formatted_amount, '1 Upcoming event', '@count Upcoming events');
    }

    return $text->__toString();
  }

  /**
   * Return the count of published Reserves.
   *
   * @return int
   */
  public function getReserves() {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'reserve');

    $count = $query->count()->execute();

    return $count;
  }
}
