(function($, Drupal) {
  'use strict';
  Drupal.behaviors.featuredCardsAdmin = {
    attach: function(context) {
      /**
       * Check the card type and whether or not a field should be hidden.
       *
       * @param $cardTypeSelect
       *   Select dom object.
       * @param $element
       *   The element to be hidden/shown.
       * @param hideForType
       *   Which Card Type the element should be hidden for.
       */
      function checkCardType($cardTypeSelect, $element, hideForType) {
        if ($cardTypeSelect.val() === hideForType || $cardTypeSelect.val() === '_none') {
          // Hide if given type is selected.
          $element.hide();
        }
        else if ($cardTypeSelect.val() !== hideForType) {
          // Show if given type is selected.
          $element.show();
        }
      }

      /**
       * Update the fields shown based on a given cardinality.
       *
       * @param cardinality
       *   The cardinality selected.
       * @param $items
       *   The items the cardinality affects.
       */
      function toggleCardinality(cardinality, $items) {
        $items.find('tbody tr:lt(' + cardinality + ')').show();
        $items.find('tbody tr:gt(' + (cardinality - 1) + ')').hide().find('input').val('');
      }

      // Get the 2 field types which will be affected by other fields.
      var $curatedItems = $('.field--name-field-cards-curated-items', context);
      var $dynamicFields = $('[class*="field--name-field-cards-dynamic-"]', context);

      // Affect the Curated Items cardinality and visibility.
      $curatedItems.once().each(function () {
        var $itemFields = $(this);

        if ($itemFields.length) {
          var $cardType = $itemFields.siblings('.field--name-field-cards-type').find('select');
          var $cardNumber = $itemFields.siblings('.field--name-field-cards-number').find('select');

          checkCardType($cardType, $itemFields, 'dynamic');
          toggleCardinality($cardNumber.val(), $itemFields);

          $cardType.change(function () {
            checkCardType($(this), $itemFields, 'dynamic');
          });

          $cardNumber.change(function () {
            toggleCardinality($(this).val(), $itemFields);
          });
        }
      });

      // Affect the Dynamic Term field visibility.
      $dynamicFields.once().each(function () {
        var $dynamicField = $(this);

        if ($dynamicField.length) {
          var $cardType = $dynamicField.siblings('.field--name-field-cards-type').find('select');

          checkCardType($cardType, $dynamicField, 'curated');

          $cardType.change(function () {
            checkCardType($cardType, $dynamicField, 'curated');
          });
        }
      });

    }
  };
})(jQuery, Drupal);
