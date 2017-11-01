<?php

namespace WildlifeTrusts\Migration;

use DateTime;
use Doctrine\DBAL\Driver\PDOConnection;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Exception;
use PDO;

class EventsMigration extends MigrationBase {

  /**
   * Specific user IDs of source nodes (used to limit the scope of the migration)
   */
  private $trustNodeIds = [];

  /**
   * ID of the 'RSWT Event Category' taxonomy on the data site
   */
  private $rswtEventCategoryTaxonomyVocabularyId;

  private $sourceLocationJsonData = [];

  private function prepLocationTitle($title) {
    $title = $this->prepString($title, FALSE);

    // Remove any text after the first comma in the title, if present
    if (strpos($title, ',') !== FALSE) {
      $title = substr($title, 0, strpos($title, ','));
    }

    // Trim location title to 50 characters
    $title = substr($title, 0, 50);

    return $title;
  }

  /**
   * Ensures locations exist ahead of events being migrated
   */
  private function processLocation($event) {
    $locationTitle = $this->prepLocationTitle($event->field_event_location[0]->value);

    if (empty($locationTitle)) {
      return;
    }

    if (!array_key_exists($locationTitle, $this->sourceLocationJsonData)) {
      $this->sourceLocationJsonData[$locationTitle] = [
        'name' => $locationTitle,
        'created' => time(),
        'changed' => time(),
        'status' => '1',
        'default_langcode' => true,
        'field_location_address' => $locationTitle,
        'field_location_county' => $this->prepString($event->field_event_county[0]->value, FALSE),
        'field_location_grid_ref' => $this->prepString($event->field_grid_ref[0]->value, FALSE),
        'field_location_postcode' => $this->prepString($event->field_postcode[0]->value, FALSE),
        'field_location_town' => $this->prepString($event->field_event_town[0]->value, FALSE),
      ];

      if ($event->mapping) {
        $this->sourceLocationJsonData[$locationTitle]['field_location_lat_long_value'] = "POINT ({$event->mapping->longitude} {$event->mapping->latitude})";
        $this->sourceLocationJsonData[$locationTitle]['field_location_lat_long_type'] = 'Point';
      }
    }
  }

  private function migrateLocations() {
    // Clear existing nodes if specified
    if ($this->clearExisting) {
      $this->logMessage("Clearing existing 'location' nodes...");
      $this->clearExistingNodes('location');
    }

    $manager = \Drupal::service('plugin.manager.migration');
    $plugins = $manager->createInstances(['initial_locations']);
    $locationMigrationEntity = reset($plugins);

    // Output JSON to a temporary file
    $tmpFile = tempnam(sys_get_temp_dir(), 'location_migrate');
    $tmpHandle = fopen($tmpFile, "w");
    fwrite($tmpHandle, json_encode(['data' => $this->sourceLocationJsonData]));
    fclose($tmpHandle);

    // Point the migration to the temporary file
    $source = $locationMigrationEntity->get('source');
    $source['data_fetcher_plugin'] = 'file';
    $source['urls'] = $tmpFile;
    $locationMigrationEntity->set('source', $source);
    $locationMigrationEntity->getIdMap()->prepareUpdate();
    $log = new MigrateMessage();
    $executable = new MigrateExecutable($locationMigrationEntity, $log);

    // Run the migration
    $result = $executable->import();

    if ($result !== MigrationInterface::RESULT_COMPLETED) {
      throw new Exception("Location migration failed (exit code: {$result})");
    }
  }

  /**
   * Calculates the event time string based on the given source event.
   */
  private function calculateTime($event) {
    $startTime = (new DateTime($event->field_event_start[0]->value))->format('H:i');
    $endTime = (new DateTime($event->field_event_start[0]->value2))->format('H:i');

    return "{$startTime} to {$endTime}";
  }

  /**
   * Calculates the 'dogs' field value based on the given source event.
   */
  private function calculateDogs($event) {
    $dogs = '';

    switch ($event->field_dogs[0]->value) {
      case 'yes on lead':
        $dogs = 'on_a_lead';
        break;
      case 'assistance dogs only':
        $dogs = 'guide_dogs_only';
        break;
      case 'no dogs please':
        $dogs = 'no_dogs_permitted';
        break;
    }

    return $dogs;
  }

  /**
   * Calculates the booking status based on the given source event.
   */
  private function calculateBookingStatus($event) {
    $bookingStatus = '';

    if ($event->field_event_fully_booked[0]->value == 1) {
      $bookingStatus = 'fully_booked';
    }
    if ($event->field_event_cancelled[0]->value == 1) {
      $bookingStatus = 'cancelled';
    }

    return $bookingStatus;
  }

  /**
   * Calculates the event type from the RSWT event category/categories on the source event
   */
  private function calculateRswtCategories($event) {
    // Map RSWT Event Category taxonomy term IDs to names
    $rswtCategories = [];

    if (!is_null($event->field_rswt_category)) {
      $rswtCategories = array_map(function ($c) {
        return $this->getTaxonomyTerm($this->rswtEventCategoryTaxonomyVocabularyId, $c->value)['termName'];
      }, $event->field_rswt_category);

      // Remove null values
      $rswtCategories = array_filter($rswtCategories);
    }

    return $rswtCategories;
  }

  /**
   * Process a source item and return a JSON object to pass to the Drupal migration
   */
  private function processEvent($event) {
    if ($event->title === '') {
      return null;
    }

    $locationTitle = $this->prepLocationTitle($event->field_event_location[0]->value);

    $data = [
      'title' => $event->title,
      'created' => time(),
      'changed' => time(),
      'default_langcode' => true,
      'path' => strtolower(str_replace(' ', '-', $event->title)),
      'field_event_about_value' => $this->cleanHtml($event->body),
      'field_event_about_format' => 'rich_text',
      'field_event_additional_info' => $event->field_event_reg_details[0]->value,
      'field_event_booking_status' => $this->calculateBookingStatus($event),
      'field_event_contact_email' => $event->field_event_contact_email[0]->value,
      'field_event_contact_name' => $event->field_event_contact_name[0]->value,
      'field_event_contact_number' => $event->field_event_contact_number[0]->value,
      'field_event_dogs' => $this->calculateDogs($event),
      'field_event_hearing_loop' => $event->field_hearing_loop[0]->value,
      'field_event_meeting_point' => $event->field_meeting_place[0]->value,
      'field_event_mobility' => $event->field_mobility[0]->value,
      'field_event_price_donation' => $event->field_event_admission_details[0]->value,
      'field_event_summary' => $event->field_summary[0]->value,
      'field_event_time' => $this->calculateTime($event),
      'field_event_wheelchair' => $event->field_wheelchair[0]->value,
      'field_event_date_value' => (new DateTime($event->field_event_start[0]->value))->format('Y-m-d'),
      'field_event_date_end_value' => (new DateTime($event->field_event_start[0]->value2))->format('Y-m-d'),
      'field_event_theme' => $this->calculateRswtCategories($event),
      'field_event_location' => $locationTitle,
      'uid' => $this->authorUserEmail,
    ];

    return $data;
  }

  /**
   * Read IDs of source events, based on the (optional) specified UIDs
   */
  private function getAllEventNodeIds($earliestStartDate = NULL, $trustNodeIds = []) {
    $earliestStartDateCriteria = (is_null($earliestStartDate) ? '' : ("AND f_event_start.field_event_start_value >= '" . $earliestStartDate->format('Y-m-d') . "'"));
    $trustNodeCriteria = (empty($trustNodeIds) ? '' : ("AND ct_event.field_trust_value IN (" . implode(', ', $trustNodeIds) . ")"));

    $sql = <<<EOS
SELECT DISTINCT(n.nid) FROM node n
LEFT JOIN content_field_event_start f_event_start ON n.nid = f_event_start.nid
LEFT JOIN content_type_event ct_event ON (n.nid = ct_event.nid)
WHERE n.status = 1 AND n.type = 'event' {$earliestStartDateCriteria} {$trustNodeCriteria}
EOS;

    $this->dataSiteDbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $this->dataSiteDbConnection->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
  }

  /**
   * Read all source events to migrate, ready for processing
   */
  protected function populateSourceData() {
    $this->logMessage("Populating taxonomy cache...");
    $this->cacheTaxonomies();

    $this->rswtEventCategoryTaxonomyVocabularyId = $this->getTaxonomyVid('RSWT Events category');

    $this->logMessage("Reading event information from data site MySQL database...");
    $eventNodeIds = $this->getAllEventNodeIds(new DateTime('-1 month'), $this->trustNodeIds);
    $totalEvents = count($eventNodeIds);

    $this->logMessage("Total events: " . $totalEvents);
    $this->logMessage("Downloading additional event details...");

    $progress = 0;
    $updateFrequencyPercentage = 5;
    $nextUpdatePercentage = $updateFrequencyPercentage;

    foreach ($eventNodeIds as $nodeId) {
      $eventDetailJson = $this->restGet("{$this->endpointUrl}/node/{$nodeId}.json");
      $eventDetail = json_decode($eventDetailJson);
      $this->processLocation($eventDetail);
      $this->sourceJsonData[] = $this->processEvent($eventDetail);

      $progress++;
      if ((($progress / $totalEvents) * 100) >= $nextUpdatePercentage) {
        $this->logMessage("{$nextUpdatePercentage}% complete");
        $nextUpdatePercentage += $updateFrequencyPercentage;
      }
    }

    $this->logMessage("Done.");

    $this->logMessage("Pre-migrating " . count($this->sourceLocationJsonData) . " locations...");
    $this->migrateLocations();

    $this->sourceJsonData = array_filter($this->sourceJsonData);
  }

  function __construct($endpointUrl, $authorEmail, $trustNodeIds, $clearExisting = false) {
    $this->endpointUrl = $endpointUrl;
    $this->authorUserEmail = $authorEmail;
    $this->trustNodeIds = $trustNodeIds;

    $this->establishDataSiteDbConnection();

    parent::__construct('initial_events', 'event', $clearExisting);
  }
}
