<?php

namespace Drupal\wildlife_newsletters_campaign_monitor\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a listing of CampaignMonitorSignups.
 *
 * @ingroup campaign_monitor_signup
 */
class CampaignMonitorSignupListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity) . ' (Machine name: ' . $entity->id() . ')';
    return $row + parent::buildRow($entity);
  }

}
