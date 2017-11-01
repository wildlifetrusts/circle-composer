/**
 * @file
 * Icon picker behavior.
 *
 */
(function ($, Drupal) {
  'use strict';

  /**
   * @type {{attach: Drupal.behaviors.iconPicker.attach}}
   */
  Drupal.behaviors.iconPicker = {
    attach: function (context) {
      var setIcon = function ($select) {
        var $iconSwitcher = $select.prev('.field-prefix').find('.icon-switcher');
        var selectValue = $select.val();

        $iconSwitcher.css('background-image','url(/themes/custom/wildlife_trust/icons/png/stat-' + selectValue + '.png');
      };

      // Set initial icon.
      setIcon($('.field--widget-options-icon-select', context).find('.form-select'));

      // Update the icon on select change.
      $('.field--widget-options-icon-select', context).find('.form-select').change(function () {
        setIcon($(this));
      });
    }
  };
})(jQuery, Drupal);
