<?php

namespace Drupal\wildlife_filters;
use DateInterval;
use DatePeriod;
use DateTime;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DateFilters.
 *
 * @package Drupal\wildlife_filters
 */
class DateFilters {
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection;
   */
  protected $database;

  /**
   * Constructs a \Drupal\error_test\Controller\ErrorTestController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Retrieve an array of Years for currently published Blog/News items.
   */
  public function getActivePublicationDateYears($type) {
    $query = $this->database->select('node__field_' . $type . '_publication_date', 'pd');
    $query->join('node_field_data', 'nfd', 'nfd.nid = pd.entity_id');
    $query->addExpression('YEAR(MIN(pd.field_' . $type . '_publication_date_value))', 'min');
    $query->addExpression('YEAR(MAX(pd.field_' . $type . '_publication_date_value))', 'max');
    $query->condition('nfd.status', NODE_PUBLISHED);
    $date_range = $query->execute()->fetch();

    $years = [];
    for ($i = $date_range->min; $i <= $date_range->max; $i++) {
      $years[$i] = $i;
    }

    return $years;
  }

  /**
   * Retrieve an array of Years for currently published Events items.
   */
  public function getActiveEventsLatestDate() {
    $query = $this->database->select('node__field_event_date', 'ed');
    $query->join('node_field_data', 'nfd', 'nfd.nid = ed.entity_id');
    $query->addExpression('MAX(ed.field_event_date_end_value)', 'max');
    $query->condition('nfd.status', NODE_PUBLISHED);
    $max_date = $query->execute()->fetchField(0);

    return $max_date;
  }

  /**
   * Create a month and year drop-down.
   */
  public function getDateFormItems($type) {
    // Populate the year field.
    $years = $this->getActivePublicationDateYears($type);

    $form['year'] = [
      '#type' => 'select',
      '#title' => t('Year'),
      '#options' => $years,
      '#empty_option' => t('- Choose a year -'),
      '#default_value' => isset($_GET['year']) ? $_GET['year'] : '',
    ];

    // Populate the month field.
    $months = DateHelper::monthNames(TRUE);

    $form['month'] = [
      '#type' => 'select',
      '#title' => t('Month'),
      '#options' => $months,
      '#empty_option' => t('- Choose a month -'),
      '#empty_value' => 'any',
      '#default_value' => isset($_GET['month']) ? $_GET['month'] : '',
    ];

    return $form;
  }
}
