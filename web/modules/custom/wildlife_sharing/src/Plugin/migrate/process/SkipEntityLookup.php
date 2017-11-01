<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\IdEntityLookup.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\migrate\process\MigrationLookup;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_skip_entity_lookup"
 * )
 */
class SkipEntityLookup extends MigrationLookup {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $entity = \Drupal::service('entity.repository')
      ->loadEntityByUuid($this->configuration['type'], $value);

    // If we found an entity, check if it was from a migration.
    if (!empty($entity)) {
      $migrated = $this->migration->getIdMap()->lookupDestinationId([$value]);
      if (empty($migrated[0])) {
        // If it wasn't migrated, do nothing.
        throw new MigrateSkipRowException();
      }
    }
    return $value;
 }
}

