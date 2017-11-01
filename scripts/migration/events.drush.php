#!/usr/bin/env drush
<?php

include drush_cwd() . '/../scripts/migration/src/MigrationBase.php';
include drush_cwd() . '/../scripts/migration/src/EventsMigration.php';

$endpointUrl = 'http://dev-wildlife-data-site.pantheonsite.io/services/trustdetails';

$trustNodeIds = [];
$trustNodeIdList = drush_get_option('trust-node-ids');

if ($trustNodeIdList) {
    $trustNodeIds = explode(',', str_replace(' ', '', $trustNodeIdList));
    $valid = array_filter($trustNodeIds, 'is_numeric');

    if (count($valid) < count($trustNodeIds)) {
        drush_print("The 'trust-node-ids' parameter should contain a comma-separated list of trust node IDs, e.g. '51623,52933'.");
        exit(1);
    }
}

$authorUserEmail = drush_get_option('content-owner-email');

if (!$authorUserEmail) {
  drush_print("Please specify the --content-owner-email='email@address.com' parameter, giving the email address of the user who should own migrated content.");
  exit(1);
}

$clearExisting = (drush_get_option('delete_existing') == 'true');

$migration = new \WildlifeTrusts\Migration\EventsMigration($endpointUrl, $authorUserEmail, $trustNodeIds, $clearExisting);
$migration->doMigration();