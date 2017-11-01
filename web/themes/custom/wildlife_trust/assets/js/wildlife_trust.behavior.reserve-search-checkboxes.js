/**
 * @file
 * Reserve Search checkboxes behaviours.
 *
 */
(function ($, Drupal) {
  'use strict';

  /**
   * Behaviour to update the reserve search selects to use checkboxes.
   *
   * @type {{attach: Drupal.behaviors.reserveSearchCheckboxes.attach}}
   */
  Drupal.behaviors.reserveSearchCheckboxes = {
    attach: function () {
      var formItems = [
        '.form__item--baby-changing',
        '.form__item--bird-hides',
        '.form__item--cafe',
        '.form__item--disabled-toilet',
        '.form__item--dogs-not',
        '.form__item--outdoor-play-area',
        '.form__item--picnic-area',
        '.form__item--shop',
        '.form__item--toilets',
        '.form__item--visitor-centre'
      ];

      $.each(formItems, function() {
        var $formItem = $(this);

        // Set the label with the class "after" so the checkboxes render the
        // right side.
        $formItem.removeClass('form__item--label-before').addClass('form__item--label-after');

        // Hide the select item.
        $formItem.find('.form__item--type-uniform-select').hide();
        var formItemDefaultValue = $formItem.find('select').val();
        var formItemName = $formItem.find('select').attr('name') + '_checkbox';
        $formItem.find('label').attr('for', formItemName);

        var $input = $('<input id="' + formItemName + '" value="1" class="form-checkbox" type="checkbox" />');

        if (formItemDefaultValue === '1' || formItemDefaultValue === 'no_dogs_permitted') {
          $input.attr('checked', 'checked');
        }

        // Add a checkbox class.
        $formItem
          .addClass('form__item--type-checkbox')
          .removeClass('form__item--type-select')
          .prepend($input)
          .find('#' + formItemName).change(function () {
            var $checkbox = $(this);
            var value = this.checked;
            var $checkboxSelectPartner = $checkbox.parent().find('select');

            if (value) {
              if (formItemName === 'dogs_not_checkbox') {
                $checkboxSelectPartner.val('no_dogs_permitted');
              }
              else {
                $checkboxSelectPartner.val('1');
              }
            }
            else {
              $checkboxSelectPartner.val('All');
            }
        });
      });
    }
  };
})(jQuery, Drupal);
