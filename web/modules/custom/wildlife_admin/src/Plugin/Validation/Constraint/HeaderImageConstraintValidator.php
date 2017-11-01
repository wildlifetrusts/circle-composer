<?php

namespace Drupal\wildlife_admin\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks an Image or Silhouette is present for Headers when required.
 */
class HeaderImageConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $background = $items->getValue();

    /** @var \Drupal\field\Entity\FieldConfig $field_definition */
    $field_definition = $items->getFieldDefinition();
    $field_name = $field_definition->getName();

    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $items->getEntity();

    if (!empty($entity->get('field_header_type')->first())) {
      $header_type = $entity->get('field_header_type')->first()->getString();
      $header_type_field_name = 'field_header_' . $header_type;

      // If the Header Type field is set to Image or Silhouette, check the
      // corresponding fields have been set.
      $type_requires_image = in_array($header_type, ['image', 'silhouette']);
      $field_is_header = $field_name == $header_type_field_name;
      $background_empty = empty($background);

      if ($type_requires_image && $field_is_header && $background_empty) {
        /** @var \Drupal\field\Entity\FieldConfig $header_type_definition */
        $header_type_definition = $entity->getFieldDefinition('field_header_type');
        /** @var \Drupal\field\Entity\FieldStorageConfig $header_type_field_storage */
        $header_type_field_storage = $header_type_definition->getFieldStorageDefinition();
        $allowed_values = $header_type_field_storage->getSetting('allowed_values');
        $type = $allowed_values[$header_type];

        $this->context->addViolation($constraint->message, array(
          '@field' => $field_definition->getLabel(),
          '@type' => $type,
        ));
      }
    }
  }
}
