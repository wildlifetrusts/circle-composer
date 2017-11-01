<?php

namespace Drupal\wildlife_paragraphs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks a Statistic item set to static has a Type and Number.
 */
class StatisticStaticConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $item_value = $items->getValue();
    /** @var \Drupal\field\Entity\FieldConfig $field_definition */
    $field_definition = $items->getFieldDefinition();
    $type = $field_definition->getLabel();

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = $items->getEntity();
    $statistic_type = $paragraph->get('field_statistic_type')->first()->getString();

    if ($statistic_type == 'static' && empty($item_value)) {
      $this->context->addViolation($constraint->message, array('@type' => $type));
    }
  }
}
