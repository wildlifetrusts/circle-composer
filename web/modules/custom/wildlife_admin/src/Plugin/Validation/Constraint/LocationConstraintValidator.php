<?php

namespace Drupal\wildlife_admin\Plugin\Validation\Constraint;

use Drupal\paragraphs\Tests\Experimental\ParagraphsExperimentalAdministrationTest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks either a Location or Reserve is present.
 */
class LocationConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $field_name = $items->getName();
    $value = $items->getValue();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $items->getEntity();
    $bundle = $node->bundle();
    $bundle_short = $bundle == 'volunteer_opportunity' ? 'voluntary' : $bundle;

    if (strpos($field_name, '_location') && empty($value)) {
      $field_has_psuedo_location_contents = isset($_POST[$field_name]['form']['entity_id']) ? $_POST[$field_name]['form']['entity_id'] : FALSE;
      $value = $field_has_psuedo_location_contents;
    }

    if ($field_name == 'field_' . $bundle_short . '_location') {
      $field_to_check = 'field_' . $bundle_short . '_reserve';
    }
    else {
      $field_to_check = 'field_' . $bundle_short . '_location';
    }

    $field_to_check_value = $node->get($field_to_check)->getValue();

    if (empty($value) && empty($field_to_check_value)) {
      $this->context->addViolation($constraint->message, array(
        '@constraint' => t('either a'),
      ));
    }
    else if (!empty($value) && !empty($field_to_check_value)) {
      $this->context->addViolation($constraint->message, array(
        '@constraint' => t('only one of'),
      ));
    }
  }
}
