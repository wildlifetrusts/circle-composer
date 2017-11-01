<?php

namespace Drupal\wildlife_paragraphs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks that Rich Text has been entered if Accompanying Text has been checked.
 */
class AccompanyingTextConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $value = $items->first()->getValue();
    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = $items->getEntity();
    $bundle = $paragraph->bundle();
    $rich_text = $paragraph->get('field_' . $bundle . '_rich_text')->getValue();

    if ($value['value'] && empty($rich_text)) {
      $this->context->addViolation($constraint->message);
    }
  }
}
