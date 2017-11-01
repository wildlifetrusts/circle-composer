<?php

namespace Drupal\wildlife_paragraphs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks a Statistic item set to static has a Type.
 *
 * @Constraint(
 *   id = "StatisticStatic",
 *   label = @Translation("Enforce the addition of a Type.", context = "Validation"),
 * )
 */
class StatisticStaticConstraint extends Constraint {
  public $message = 'You must populate the "@type" field if you have set a Statistic Item to "Static".';
}
