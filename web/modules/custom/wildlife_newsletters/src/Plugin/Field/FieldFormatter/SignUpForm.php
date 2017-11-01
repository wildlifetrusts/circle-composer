<?php

namespace Drupal\wildlife_newsletters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'signup_form' formatter.
 *
 * @FieldFormatter(
 *   id = "signup_form",
 *   label = @Translation("Sign Up Form"),
 *   field_types = {
 *     "entity_reference",
 *   }
 * )
 */
class SignUpForm extends FormatterBase {

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Field\EntityReferenceFieldItemList $items
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entities = $items->referencedEntities();

    foreach ($entities as $delta => $item) {
      $entity_type = $item->getEntityTypeId();

      if ($entity_type == 'campaign_monitor_signup') {
        $form = $item->get('form');

        $elements[$delta] = [
          '#type' => 'inline_template',
          '#template' => $form['value'],
        ];
      }
      elseif ($entity_type == 'mailchimp_signup') {
        $form = new \Drupal\mailchimp_signup\Form\MailchimpSignupPageForm();

        $form_id = 'mailchimp_signup_subscribe_block_' . $item->id . '_form';
        $form->setFormID($form_id);
        $form->setSignup($item);

        $form_render_array = \Drupal::formBuilder()->getForm($form);

        // Update the submit button.
        /** @var \Drupal\paragraphs\Entity\Paragraph $parent */
        $parent = $items->getParent()->getValue();
        $custom_submit_text = $parent->get('field_newsletter_mailchimp_btn');

        if (!$custom_submit_text->isEmpty()) {
          $form_render_array['actions']['submit']['#value'] = $custom_submit_text->getString();
        }

        $elements[$delta] = $form_render_array;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available on entity types that reference
    // newsletter entities.
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    $newsletter_entities = [
      'campaign_monitor_signup',
      'mailchimp_signup',
    ];

    return in_array($target_type, $newsletter_entities);
  }
}
