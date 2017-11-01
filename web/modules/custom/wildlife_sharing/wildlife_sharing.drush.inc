<?php


/**
 * Implements hook_drush_command().
 *
 * Adds paragraph cleaner command.
 */
function wildlife_sharing_drush_command() {
  $items = [];

  $items['paragraph-cleaner'] = [
    'description' => 'Delete any orphaned paragraph items',
    'drupal dependencies' => ['wildlife_sharing'],
    'aliases' => ['pc'],
  ];

  return $items;
}

function drush_wildlife_sharing_paragraph_cleaner() {
  Drupal::service('wildlife_sharing.paragraph_cleaner')->removeOrphanParagraphs();
}
