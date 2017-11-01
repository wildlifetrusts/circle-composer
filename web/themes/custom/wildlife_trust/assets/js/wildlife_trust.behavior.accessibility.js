/**
 * @file
 * Accessibility fixes.
 */
(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.preventStuckFocusStyles = {
    attach: function (context) {
      // Prevent focus styling hanging around for too long after a mouse click.
      $('a, button', context).click(function (event) {
        if (event.screenY > 0) {
          event.currentTarget.blur();
        }
      });
    }
  };
})(jQuery, Drupal);
