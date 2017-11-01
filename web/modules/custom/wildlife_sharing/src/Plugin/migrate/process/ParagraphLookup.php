<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\ParagraphLookup.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_paragraph_lookup"
 * )
 */
class ParagraphLookup extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = !empty($value['id']) ? $value['id'] : $value;
    if (empty($value)) {
      return NULL;
    }

    if (is_array($value) && !empty($value[0])) {
      $return = [];

      foreach ($value as $value_item) {
        $return[] = $this->transform($value_item, $migrate_executable, $row, $destination_property);
      }

      return $return;
    }

    $entity = \Drupal::service('entity.repository')->loadEntityByUuid('paragraph', $value);
    if (empty($entity) || !$entity instanceof Paragraph) {
      return NULL;
    }

    return [
      'target_id' => $entity->id(),
      'target_revision_id' => $entity->getRevisionId(),
    ];
  }
}

