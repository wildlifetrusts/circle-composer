<?php

namespace Drupal\wildlife_paragraphs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that Rich Text has been entered if Accompanying Text has been checked.
 *
 * @Constraint(
 *   id = "AccompanyingText",
 *   label = @Translation("Enforce the addition of Rich Text when Accompanying Text is checked", context = "Validation"),
 * )
 */
class AccompanyingTextConstraint extends Constraint {
  public $message = 'You must add Rich Text if you have checked the Accompanying Text checkbox.';
}
