<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\IdEntityLookup.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
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
 *   id = "wildlife_sharing_id_entity_lookup"
 * )
 */
class IdEntityLookup extends EntityLookup {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = !empty($value['id']) ? $value['id'] : $value;
    $return = parent::transform($value, $migrate_executable, $row, $destination_property);
    return $return;
  }
}

