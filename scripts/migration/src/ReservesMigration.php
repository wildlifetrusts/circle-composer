<?php

namespace WildlifeTrusts\Migration;

use Solarium\Core\Client\Adapter\Guzzle;

class ReservesMigration extends MigrationBase {

  /**
   * Specific user IDs of source nodes (used to limit the scope of the migration)
   */
  private $uids;

  /**
   * Calculates a text string based on various start/end dates in the source event
   */
  private function calculateBestTimeToVisit($reserve) {
    $bestTimeToVisit = [];

    $visitRanges = [
      [
        'start' => $reserve->field_visit_start_1[0]->value,
        'end' => $reserve->field_visit_end_1[0]->value,
      ],
      [
        'start' => $reserve->field_visit_start_2[0]->value,
        'end' => $reserve->field_visit_end_2[0]->value,
      ],
      [
        'start' => $reserve->field_visit_start_3[0]->value,
        'end' => $reserve->field_visit_end_3[0]->value,
      ],
    ];

    if (($visitRanges[0]['start'] == 'Jan') && ($visitRanges[0]['end'] == 'Dec')) {
      $bestTimeToVisit[] = 'All year round';
    } else {
      foreach ($visitRanges as $range) {
        if ($range['start'] && $range['end']) {
          $fullStart = $this->monthAbbreviationToFull($range['start']);
          $fullEnd = $this->monthAbbreviationToFull($range['end']);

          $bestTimeToVisit[] = "{$fullStart} to {$fullEnd}";
        }
      }
    }

    return implode(', ', $bestTimeToVisit);
  }

  /**
   * Calculates the 'dogs' field value based on the given source event.
   */
  private function calculateDogs($reserve) {
    $dogs = '';

    switch ($reserve->field_fac_dogs[0]->value) {
      case 'lead':
        $dogs = 'on_a_lead';
        break;
      case 'control':
        $dogs = 'under_effective_control';
        break;
      case 'guide':
        $dogs = 'guide_dogs_only';
        break;
      case 'yes':
        $dogs = 'dogs_permitted';
        break;
      case 'no':
        $dogs = 'no_dogs_permitted';
        break;
    }

    return $dogs;
  }

  /**
   * Process a source item and return a JSON object to pass to the Drupal migration
   */
  private function processReserve($reserve) {
    if ($reserve->title === '') {
      return null;
    }

    $booleanFields = [
      'field_reserve_baby_changing' => 'field_fac_babychange',
      'field_reserve_cafe_refreshments' => 'cafe',
      'field_reserve_disabled_toilet' => 'field_fac_d_toilet',
      'field_reserve_picnic_area' => 'field_fac_picnic',
      'field_reserve_shop' => 'field_fac_shop',
      'field_reserve_toilets' => 'field_fac_toilet',
      'field_reserve_visitor_centre' => 'field_fac_visitcentre',
    ];

    $data = [
      'title' => $reserve->title,
      'created' => time(),
      'changed' => time(),
      'status' => '1',
      'moderation_state' => 'published',
      'sticky' => false,
      'promote' => false,
      'default_langcode' => true,
      'path' => strtolower(str_replace(' ', '-', $reserve->title)),
      'colour_scheme' => '',
      'field_reserve_about_value' => $this->cleanHtml($reserve->body),
      'field_reserve_about_format' => 'rich_text',
      'field_reserve_access_value' => $this->cleanHtml($reserve->field_fac_accessinfo[0]->value),
      'field_reserve_access_format' => 'rich_text',
      'field_reserve_address' => $reserve->address[0]->value,
      'field_reserve_best_time_to_visit' => $this->calculateBestTimeToVisit($reserve),
      'field_reserve_contact_email' => $reserve->field_contact_email[0]->value,
      'field_reserve_contact_name' => $reserve->field_contact[0]->value,
      'field_reserve_contact_number' => $reserve->field_contact_num[0]->value,
      'field_reserve_county' => $reserve->field_country[0]->value,
      'field_reserve_dogs' => $this->calculateDogs($reserve),
      'field_reserve_entry_fee' => $this->prepString($reserve->field_fac_amount[0]->value, true),
      'field_reserve_flickr' => $reserve->field_flickr_group[0]->value,
      'field_reserve_grazing_animals' => $this->prepString($reserve->field_grazing[0]->value, true),
      'field_reserve_lat_long_value' => "POINT ({$reserve->mapping->longitude} {$reserve->mapping->latitude})",
      'field_reserve_lat_long_type' => 'Point',
      'field_reserve_opening_times' => $this->prepString($reserve->field_fac_opening[0]->value, true),
      'field_reserve_parking_info' => $reserve->field_fac_parkinginfo->item_value,
      'field_reserve_postcode' => $reserve->field_postcode[0]->value,
      'field_reserve_summary' => $reserve->field_short_desc[0]->value,
      'field_reserve_town' => $reserve->field_town[0]->value,
      'field_reserve_walking_trails_value' => $reserve->field_walking[0]->value,
      'field_reserve_deep_link_uri' => $reserve->field_reserve_url[0]->value,
      'uid' => $this->authorUserEmail,
    ];

    $this->appendTaxonomyTerms($reserve, $data, [
      'Species' => 'field_reserve_species',
      'Great for' => 'field_reserve_great_for',
      'Environmental Designation' => 'field_reserve_env_designation',
    ]);

    foreach ($booleanFields as $field => $source) {
      if ($reserve->$source[0]->value == 'yes') {
        $data[$field] = true;
      }
      if ($reserve->$source[0]->value == 'no') {
        $data[$field] = false;
      }
    }

    return $data;
  }

  /**
   * Read IDs of source reserves, based on the (optional) specified UIDs
   */
  private function getFullReserveList() {
    $allReserves = [];

    // Get reserve details, for the given UIDs if specified
    foreach ($this->uids as $uid) {
      $uidParam = (is_null($uid) ? '' : "&parameters[uid]={$uid}");
      $reserves = $this->getPagedResponse("/node.json?parameters[type]=reserve{$uidParam}&fields=nid");
      $allReserves = array_merge($allReserves, $reserves);
    }

    return $allReserves;
  }

  /**
   * Read all source reserves to migrate, ready for processing
   */
  protected function populateSourceData() {
    $this->logMessage("Populating taxonomy cache...");
    $this->cacheTaxonomies();

    $this->logMessage("Reading reserve information from REST endpoint...");
    $reserves = $this->getFullReserveList();
    $totalReserves = count($reserves);

    $this->logMessage("Total reserves: " . $totalReserves);
    $this->logMessage("Downloading additional reserve details...");

    $progress = 0;
    $updateFrequencyPercentage = 5;
    $nextUpdatePercentage = $updateFrequencyPercentage;

    foreach ($reserves as $reserve) {
      $reserveDetailJson = $this->restGet($reserve->uri);
      $reserveDetail = json_decode($reserveDetailJson);
      $this->sourceJsonData[] = $this->processReserve($reserveDetail);

      $progress++;
      if ((($progress / $totalReserves) * 100) >= $nextUpdatePercentage) {
        $this->logMessage("{$nextUpdatePercentage}% complete");
        $nextUpdatePercentage += $updateFrequencyPercentage;
      }
    }

    $this->logMessage("Done.");

    $this->sourceJsonData = array_filter($this->sourceJsonData);
  }

  function __construct($endpointUrl, $authorEmail, $uids) {
    $this->endpointUrl = $endpointUrl;
    $this->authorUserEmail = $authorEmail;
    $this->uids = $uids;

    parent::__construct('initial_reserves', 'reserve');
  }
}