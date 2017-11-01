/**
 * @file
 * Spotlight animatable background.
 *
 */
(function ($) {
  'use strict';

  /**
   * Behaviour to determine whether the background will animate.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustSpotlightAnimatableBackground.attach}}
   */
  Drupal.behaviors.wildlifeTrustSpotlightAnimatableBackground = {
    attach: function () {
      // This "fix" specifically targets IE9 - 11. It is possible this fix
      // could break at a later date since MS could update things so this
      // detection does not work.
      var msie = parseInt((/msie (\d+)/.exec(navigator.userAgent.toLowerCase()) || [])[1]);

      if (isNaN(msie)) {
        msie = parseInt((/trident\/.*; rv:(\d+)/.exec(navigator.userAgent.toLowerCase()) || [])[1]);
        
        if (msie >= 9 && msie <= 11) {
          $('.spotlight').removeClass('background-animate');
        }
      }
    }
  };

})(jQuery);
