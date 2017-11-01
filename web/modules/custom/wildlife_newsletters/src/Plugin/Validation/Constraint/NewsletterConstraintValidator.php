<?php

namespace Drupal\wildlife_newsletters\Plugin\Validation\Constraint;

use Drupal\Core\Url;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks that a Newsletter has been set for the given type.
 */
class NewsletterConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $field_name = $items->getName();
    $count = $items->count();

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = $items->getEntity();
    $campaign_type = $paragraph->get('field_newsletter_campaign_type')->getString();

    $campaign_type_mappings = [
      'mailchimp' => [
        'provider' => 'Mailchimp',
        'admin_page' => Url::fromRoute('mailchimp_signup.admin')->toString(),
        'field' => 'field_newsletter_mailchimp_form',
      ],
      'campaign_monitor' => [
        'provider' => 'Campaign Monitor',
        'admin_page' => Url::fromRoute('campaign_monitor_signup.admin')->toString(),
        'field' => 'field_newsletter_cm_form',
      ],
    ];

    if ($field_name == $campaign_type_mappings[$campaign_type]['field'] && !$count) {
      $provider = $campaign_type_mappings[$campaign_type]['provider'];
      $admin_page = $campaign_type_mappings[$campaign_type]['admin_page'];

      $this->context->addViolation($constraint->message, [
        '@provider' => $provider,
        '@admin_page' => $admin_page,
      ]);
    }
  }
}
