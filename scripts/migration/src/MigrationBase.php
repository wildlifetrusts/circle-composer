<?php

namespace WildlifeTrusts\Migration;

use DateTime;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_tools\MigrateExecutable;
use Exception;
use PDO;
use PDOException;

class MigrationBase {

  protected $dataSiteDbDetails = [
    'host' => 'dbserver.dev.46ca4102-0eab-48c5-81a5-72057f0c2875.drush.in',
    'port' => '10933',
    'db_name' => 'pantheon',
    'username' => 'pantheon',
    'password' => '577f745731ad41babf21ae02b4ef470b',
  ];

  protected $dataSiteDbConnection;

  /**
   * Array of JSON data to be passed to the Drupal migration
   */
  protected $sourceJsonData = [];

  /**
   * Email address of the Drupal user to import data as
   */
  protected $authorUserEmail;

  /**
   * REST endpoint base (for migrations which pull from the WT data site)
   */
  protected $endpointUrl;

  /**
   * Taxonomy cache, built up for quick referencing of taxonomy vocabularies/terms
   */
  protected $taxonomies = [];

  /**
   * Name of the Drupal Migrate entity used by this migration
   */
  protected $migrationEntityName;

  /**
   * Actual instance of the migration entity
   */
  protected $migrationEntity;

  /**
   * Name of the Drupal content type to clear (optionally) before performing the migration
   */
  protected $contentTypeToClear;

  /**
   * Whether existing content should be cleared before migrating (boolean)
   */
  protected $clearExisting;

  /**
   * Connect to the data site's MySQL database directly
   */
  protected function establishDataSiteDbConnection() {
    try {
      $this->dataSiteDbConnection = new PDO("mysql:host={$this->dataSiteDbDetails['host']};port={$this->dataSiteDbDetails['port']};dbname={$this->dataSiteDbDetails['db_name']}", $this->dataSiteDbDetails['username'], $this->dataSiteDbDetails['password']);
    }
    catch (PDOException $ex) {
      drush_die("Couldn't establish a connection to the data site's MySQL database: {$ex->getMessage()}");
    }
  }

  /**
   * Trim each string in an array
   */
  protected function trimValues(&$array) {
    $array = array_map(function ($v) {
      return trim($v);
    }, $array);
  }

  /**
   * Attempt to convert a month abbreviation (e.g. 'Jan') to the full month name ('January')
   */
  protected function monthAbbreviationToFull($abbreviation) {
    $dt = DateTime::createFromFormat('!M', $abbreviation);
    if ($dt === false) {
      // If the abbreviation couldn't be expanded, return the original
      return $abbreviation;
    }

    return $dt->format('F');
  }

  /**
   * Log a message (to stdout)
   */
  protected function logMessage($msg) {
    echo "$msg\n";
  }

  /**
   * Clean up HTML input
   */
  protected function cleanHtml($html) {
    return strip_tags($html);
  }

  /**
   * Get a taxonomy vocabulary ID from its name, from the taxonomy cache
   */
  protected function getTaxonomyVid($vocabularyName) {
    foreach ($this->taxonomies as $vId => $v) {
      if ($v['name'] == $vocabularyName) {
        return $vId;
      }
    }

    return null;
  }

  /**
   * Get a taxonomy term name based on the given vocabulary and term IDs, from the taxonomy cache
   */
  protected function getTaxonomyTerm($vid, $tid) {
    return [
      'vocabularyName' => $this->taxonomies[$vid]['name'],
      'termName' => $this->taxonomies[$vid]['terms'][$tid]['name'],
    ];
  }

  /**
   * Get a taxonomy vocabulary ID from its name, from the taxonomy cache
   */
  protected function cacheTaxonomies() {
    // Get list of taxonomy vocabularies
    $vocabs = $this->getPagedResponse("/taxonomy_vocabulary.json?fields=vid,name");

    // For each vocabulary, cache its terms
    foreach ($vocabs as $v) {
      $this->taxonomies[$v->vid] = [
        'name' => $v->name,
        'terms' => [],
      ];

      $terms = $this->getPagedResponse("/taxonomy_term.json?parameters[vid]={$v->vid}&fields=tid,vid,name");

      foreach ($terms as $t) {
        $this->taxonomies[$v->vid]['terms'][$t->tid] = ['name' => $t->name];
      }
    }
  }

  /**
   * Given a node from a Drupal Services REST call and a source JSON object,
   * populate the taxonomy fields as indicated by the given mapping
   */
  protected function appendTaxonomyTerms($node, &$data, $vocabularyFieldMapping) {
    foreach ($node->taxonomy as $term) {
      $t = $this->getTaxonomyTerm($term->vid, $term->tid);

      foreach ($vocabularyFieldMapping as $vocabulary => $field) {
        if ($t['vocabularyName'] == $vocabulary) {
          if (!array_key_exists($field, $data)) {
            $data[$field] = [];
          }

          $data[$field][] = $t['termName'];
        }
      }
    }
  }

  /**
   * Call a Drupal Services REST function and retrieve all pages of results
   */
  protected function getPagedResponse($path, $pageLimit = null) {
    $currentPage = 0;
    $allResults = [];

    do {
      $batch = $this->restGet("{$this->endpointUrl}{$path}&page={$currentPage}");
      $batch = json_decode($batch);
      $allResults = array_merge($allResults, $batch);

      $currentPage++;
    } while (count($batch) > 0 && ((is_null($pageLimit) || $currentPage < $pageLimit)));

    return $allResults;
  }

  /**
   * Prepare a string by trimming excess whitespace and optionally
   * converting the first character to upper case
   */
  protected function prepString($string, $ucFirst = false) {
    if (is_null($string)) {
      return '';
    }

    $string = trim($string);

    if ($ucFirst) {
      $string = ucfirst($string);
    }

    return $string;
  }

  /**
   * Use PHP's CURL library to make a HTTP request
   */
  protected function restGet($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']); // Accept JSON response
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($http_code == 200) {
      return $response;
    } else {
      $http_message = curl_error($curl);
      throw new Exception("Couldn't retrieve data from REST API (URL: {$url}): {$http_message}");
    }
  }

  /**
   * Clear existing nodes of the given content type
   */
  protected function clearExistingNodes($contentType) {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', $contentType)
      ->execute();

    $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
    $entities = $storage_handler->loadMultiple($nids);
    $storage_handler->delete($entities);
  }

  /**
   * Populate $sourceJsonData array with JSON objects suitable for migrating (overridden in subclass)
   */
  protected function populateSourceData() {

  }

  /**
   * Prepare the Drupal Migrate entity
   */
  private function prepareMigration() {
    $manager = \Drupal::service('plugin.manager.migration');
    $plugins = $manager->createInstances([$this->migrationEntityName]);
    $this->migrationEntity = reset($plugins);

    if ($this->migrationEntity == FALSE) {
      throw new Exception("Couldn't instantiate the migration entity '{$this->migrationEntityName}'.");
    }
  }

  /**
   * Determine whether the Drupal Migration is in an 'idle' state
   */
  public function isMigrationIdle() {
    return ($this->migrationEntity->getStatus() == MigrationInterface::STATUS_IDLE);
  }

  /**
   * Reset the Drupal Migration back to the 'idle' state
   */
  public function resetMigrationStatus() {
    return ($this->migrationEntity->setStatus(MigrationInterface::STATUS_IDLE));
  }

  /**
   * Pass the JSON stored in the sourceJsonData array to the Drupal Migrate entity
   */
  private function migrateData() {
    // Output JSON to a temporary file
    $tmpFile = tempnam(sys_get_temp_dir(), 'migrate');
    $tmpHandle = fopen($tmpFile, "w");
    fwrite($tmpHandle, json_encode(['data' => $this->sourceJsonData]));
    fclose($tmpHandle);

    // Point the migration to the temporary file
    $source = $this->migrationEntity->get('source');
    $source['data_fetcher_plugin'] = 'file';
    $source['urls'] = $tmpFile;
    $this->migrationEntity->set('source', $source);
    $this->migrationEntity->getIdMap()->prepareUpdate();
    $log = new MigrateMessage();
    $executable = new MigrateExecutable($this->migrationEntity, $log);

    // Run the migration
    $result = $executable->import();

    if ($result !== MigrationInterface::RESULT_COMPLETED) {
      throw new Exception("Migration failed (exit code: {$result})");
    }

    return true;
  }

  /**
   * Undertake all steps involved in the migration
   */
  public function doMigration() {
    // Populate the $sourceJsonData array with items, processing data en route
    $this->populateSourceData();

    // Clear existing nodes if specified
    if ($this->clearExisting) {
      $this->logMessage("Clearing existing '{$this->contentTypeToClear}' nodes...");
      $this->clearExistingNodes($this->contentTypeToClear);
    }

    $this->logMessage('Importing ' . count($this->sourceJsonData) . ' items...');

    // Run the Drupal migration
    $this->migrateData();
    $this->logMessage('Migration complete.');
  }

  function __construct($migrationName, $nodeToClear = null, $clearExisting = false) {
    $this->migrationEntityName = $migrationName;
    $this->contentTypeToClear = $nodeToClear;
    $this->clearExisting = $clearExisting;

    $this->prepareMigration();

    // Ask the user whether to reset the status of the migration if necessary (it may
    // be in a non-idle state by accident, or it could actually be running elsewhere)
    if (!$this->isMigrationIdle()) {
      if (drush_confirm("The '{$this->migrationEntityName}' migration is not in an idle state. Would you like to reset the status to allow migration to continue?")) {
        $this->resetMigrationStatus();
      } else {
        drush_print("Cannot continue as the migration is not in an idle state.");
        drush_user_abort();
      }
    }
  }
}