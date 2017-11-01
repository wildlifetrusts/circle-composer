<?php

namespace Drupal\wildlife_paragraphs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks an Image or Silhouette is present for Spotlights when required.
 */
class SpotlightImageConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $image = $items->getValue();
    /** @var \Drupal\field\Entity\FieldConfig $field_definition */
    $field_definition = $items->getFieldDefinition();
    $type = $field_definition->getLabel();

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = $items->getEntity();
    $display_type = $paragraph->get('field_spotlight_display_type')->first()->getString();
    $image_type = $paragraph->get('field_spotlight_image_type')->first()->getString();

    if (empty($image)) {
      // Image checks.
      if ($items->getName() == 'field_spotlight_image') {
        if ($display_type == 'overlay' && $image_type == 'image') {
          $constraints = 'Display type "Overlay" and Image type "Background"';
        }
        elseif ($display_type == 'separate') {
          $constraints = 'Display type "Separate"';
        }
      }

      // Spotlight checks.
      if ($items->getName() == 'field_spotlight_silhouette') {
        if ($display_type == 'silhouette') {
          $constraints = 'Image type "Silhouette"';
        }
      }

      if (isset($constraints)) {
        $this->context->addViolation($constraint->message, array(
          '@type' => $type,
          '@constraints' => $constraints,
        ));
      }

    }
  }
}
