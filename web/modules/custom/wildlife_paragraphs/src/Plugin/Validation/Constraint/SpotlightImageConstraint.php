<?php

namespace Drupal\wildlife_paragraphs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks an Image or Silhouette is present for Spotlights when required.
 *
 * @Constraint(
 *   id = "SpotlightImage",
 *   label = @Translation("Enforce the addition of an Image or Silhouette under certain conditions.", context = "Validation"),
 * )
 */
class SpotlightImageConstraint extends Constraint {
  public $message = 'You must populate the "@type" field if you have set @constraints.';
}
