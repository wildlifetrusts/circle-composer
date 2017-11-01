<?php

namespace Drupal\wildlife_local_customisation\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Local customisation entities.
 */
class LocalCustomisationViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Create a relationship between Local customisation and Nodes.
    $data['node']['uuid'] = [
      'title' => t('Local customisation'),
      'help' => t('Relate local customisation content to the node content'),
      'relationship' => [
        'base' => 'local_customisation_field_data',
        'base field' => 'node_uuid',
        'id' => 'standard',
        'label' => t('Local customisation'),
      ],
    ];

    // Update blacklist field to allow null items (so that none localisation
    // customisation is not filtered out).
    $data['local_customisation__field_local_blacklist']['field_local_blacklist_value']['filter']['accept null'] = TRUE;

    return $data;
  }

}
