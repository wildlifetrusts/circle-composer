<?php

namespace Drupal\wildlife_newsletters\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\mailchimp_signup\Controller\MailchimpSignupListBuilder;

/**
 * Provides a listing of MailchimpSignups.
 *
 * @ingroup mailchimp_signup
 */
class NewsletterSignupListBuilder extends MailchimpSignupListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    unset($header['display_modes']);
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    unset($row['display_modes']);
    return $row;
  }

}
