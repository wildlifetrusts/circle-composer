<?php

namespace WildlifeTrusts\Migration;

class SpeciesMigration extends MigrationBase {

  /**
   * Google Sheet CSV export URL (returns CSV data)
   */
  private $csvUrl;

  /**
   * Process a source CSV row and return a JSON object to pass to the Drupal migration
   */
  private function processCsvRow($row, $headers) {
    // Construct an array with headers as keys and row data as values
    $data = array_combine($headers, $row);

    // Don't process empty rows, or rows where the name is empty
    if ($data['Name'] === '') {
      return null;
    }

    // Calculate/compose the "When to see" field value
    $whenToSee = "{$data['When to see start']} to {$data['When to see end']}";

    if (strlen($data['When to see more']) > 0) {
      $whenToSee .= "\n{$data['When to see more']}";
    }

    return [
      'title' => $data['Name'],
      'created' => time(),
      'changed' => time(),
      'status' => '1',
      'moderation_state' => 'published',
      'sticky' => false,
      'promote' => false,
      'default_langcode' => true,
      'path' => strtolower(str_replace(' ', '-', $data['Name'])),
      'field_species_about' => $data['About'],
      'field_species_did_you_know' => $data['Did you know'],
      'field_species_distribution' => $data['Distribution'],
      'field_species_help' => $data['How you can help'],
      'field_species_identify' => $data['How to identify'],
      'field_species_scientific_name' => $data['Scientific name'],
      'field_species_statistics' => $data['Statistics'],
      'field_species_summary' => $data['Summary'],
      'field_species_when_to_see' => $whenToSee,
      'field_species_species' => $data['Category'],
      'field_species_conservation' => $data['Conservation status'],
      'uid' => $this->authorUserEmail,
    ];
  }

  /**
   * Read all source species to migrate, ready for processing
   */
  protected function populateSourceData() {
    if (($csvHandle = fopen($this->csvUrl, "r")) !== false) {
      // Get the header row and trim all fields
      $headers = fgetcsv($csvHandle, 10000, ",");
      $this->trimValues($headers);

      // Get all the data rows
      while (($row = fgetcsv($csvHandle, 10000, ",")) !== false) {
        $this->trimValues($row);
        $this->sourceJsonData[] = $this->processCsvRow($row, $headers);
      }

      fclose($csvHandle);
    }

    $this->sourceJsonData = array_filter($this->sourceJsonData);
  }

  function __construct($csvUrl, $authorEmail, $clearExisting = false) {
    $this->csvUrl = $csvUrl;
    $this->authorUserEmail = $authorEmail;

    parent::__construct('initial_species', 'species', $clearExisting);
  }
}
