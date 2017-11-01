/**
 * @file
 * Responsive Background images.
 *
 */
(function ($, Drupal) {
  'use strict';

  var applyBackground = function($element, backgroundUrl) {
    $element.css('background-image', 'url(\'' + backgroundUrl + '\')');
  };

  /**
   * Behaviour to update backgrounds of components at different breakpoints.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustResponsiveBackgroundImages.attach}}
   */
  Drupal.behaviors.wildlifeTrustResponsiveBackgroundImages = {
    attach: function (context, settings) {
      var breakpoints = settings.breakpoints;
      var $components = $('.responsive-component', context);

      $components.once().each(function () {
        var $component = $(this);

        if ($component[0].hasAttribute('data-background-default')) {
          enquire.register('all and (max-width:479px)', {
            match : function() {
              applyBackground($component, $component.data('background-default'));
            }
          });
        }

        $.each(breakpoints, function (label, breakpoint) {
          if ($component[0].hasAttribute('data-background-' + label) && label !== 'default') {
            var backgroundImage = 'background-' + label;
            enquire.register(breakpoint, {
              match : function() {
                applyBackground($component, $component.data(backgroundImage));
              }
            });
          }
        });
      });
    }
  };

})(jQuery, Drupal);
