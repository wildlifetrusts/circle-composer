/**
 * @file
 * Header behaviors.
 *
 */
(function ($, Drupal) {
  'use strict';

  // Calculate whether the menu fits or not.
  var menuFit = function() {
    var $menu = $('.menu--main');

    // Set initial menu data value.
    if (!$menu.data('original-width')) {
      $menu.attr('data-original-width', $menu.outerWidth());
    }

    var $navigation = $('.l-region--navigation');
    var headerWidth = $('.l-header').innerWidth();
    var minBrandingWidth = 500;
    var maxBrandingSpace = headerWidth - $menu.data('original-width');
    var separateClass = 'l-region--navigation--separate';

    if (maxBrandingSpace < minBrandingWidth) {
      $navigation.addClass(separateClass);
    }
    else {
      $navigation.removeClass(separateClass);
    }
  };

  Drupal.behaviors.header = {
    attach: function (context) {
      $('.branding__logo', context).once().each(function () {
        var $brandingLogo = $(this);
        var $brandingName = $brandingLogo.next('.branding__name');

        if ($brandingName.length) {
          $(window).load(function () {
            menuFit();
          });

          $(window).on('resize', Drupal.debounce(menuFit, 150));
        }
      });
    }
  };
})(jQuery, Drupal);
