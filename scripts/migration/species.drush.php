#!/usr/bin/env drush
<?php

include drush_cwd() . '/../scripts/migration/src/MigrationBase.php';
include drush_cwd() . '/../scripts/migration/src/SpeciesMigration.php';

$csvUrl = 'https://docs.google.com/spreadsheets/d/14RB_lWtjYe0hWGJJVmmY8gnKFlYx0wk1BW3tTG0pTqs/export?format=csv';

$authorUserEmail = drush_get_option('content-owner-email');

if (!$authorUserEmail) {
  drush_print("Please specify the --content-owner-email='email@address.com' parameter, giving the email address of the user who should own migrated content.");
  exit(1);
}

$clearExisting = (drush_get_option('delete_existing') == 'true');

$migration = new \WildlifeTrusts\Migration\SpeciesMigration($csvUrl, $authorUserEmail, $clearExisting);
$migration->doMigration();