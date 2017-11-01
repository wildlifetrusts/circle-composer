<?php

namespace Drupal\wildlife_admin\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks either a Location or Reserve is present for certain content types.
 *
 * @Constraint(
 *   id = "Location",
 *   label = @Translation("Enforce the presence of either Location or Reserve.", context = "Validation"),
 * )
 */
class LocationConstraint extends Constraint {
  public $message = 'You must populate @constraint Location or Reserve.';
}
