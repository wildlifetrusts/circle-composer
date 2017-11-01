<?php

namespace Drupal\wildlife_admin\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks an Image or Silhouette is present for Headers when required.
 *
 * @Constraint(
 *   id = "HeaderImage",
 *   label = @Translation("Enforce the addition of an Image or Silhouette under certain conditions.", context = "Validation"),
 * )
 */
class HeaderImageConstraint extends Constraint {
  public $message = 'You must populate the "@field" field if you have set the Header type to "@type".';
}
