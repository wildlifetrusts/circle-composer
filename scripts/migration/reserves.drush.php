#!/usr/bin/env drush
<?php

include drush_cwd() . '/../scripts/migration/src/MigrationBase.php';
include drush_cwd() . '/../scripts/migration/src/ReservesMigration.php';

$endpointUrl = 'http://dev-wildlife-data-site.pantheonsite.io/services/trustdetails';

$uids = [null];
$uidList = drush_get_option('uids');

if ($uidList) {
    $uids = explode(',', str_replace(' ', '', $uidList));
    $valid = array_filter($uids, 'is_numeric');

    if (count($valid) < count($uids)) {
        drush_print("The 'uids' parameter should contain a comma-separated list of user IDs, e.g. '181,256,132'.");
        exit(1);
    }
}

$authorUserEmail = drush_get_option('content-owner-email');

if (!$authorUserEmail) {
    drush_print("Please specify the --content-owner-email='email@address.com' parameter, giving the email address of the user who should own migrated content.");
    exit(1);
}

$clearExisting = (drush_get_option('delete_existing') == 'true');

$migration = new \WildlifeTrusts\Migration\ReservesMigration($endpointUrl, $authorUserEmail, $uids, $clearExisting);
$migration->doMigration();