<?php

namespace Drupal\wildlife_newsletters\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that a Newsletter has been set for the given type.
 *
 * @Constraint(
 *   id = "Newsletter",
 *   label = @Translation("Enforce the presence of either a Mailchimp or Campaign Monitor newsletter.", context = "Validation"),
 * )
 */
class NewsletterConstraint extends Constraint {
  public $message = 'You must choose a @provider sign-up form. Admins can add new sign-up forms on the <a href="@admin_page">@provider Signup Forms</a> page.';
}
