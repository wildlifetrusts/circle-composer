/**
 * @file
 * Make the flickr component responsive.
 */
(function ($, Drupal) {
  'use strict';

  /**
   * @type {{attach: Drupal.behaviors.wildlifeTrustResponsiveFlickr.attach}}
   */
  Drupal.behaviors.wildlifeTrustResponsiveFlickr = {
    attach: function (context, settings) {
      var flickrElements = settings.flickr_embed;
      var embedWidths = [];
      var oldWidths = [];
      var newWidths = [];
      var initialLoad = [];

      $.each(flickrElements, function (id) {
        initialLoad[id] = true;
      });

      function replaceFlickr() {
        $('.field--name-field-flickr-embed-code').each(function() {
          var $embedContainer = $(this);
          var id = $embedContainer.data('id');
          var $embedInner = $embedContainer.find('.field__item');
          oldWidths[id] = newWidths[id];
          newWidths[id] = $embedContainer.innerWidth();

          if (initialLoad[id]) {
            newWidths[id] = $embedContainer.innerWidth();
            var $element = $(flickrElements[id]);
            embedWidths[id] = $element.find('img').attr('width');
            initialLoad[id] = false;
          }
          else {
            // Check if we actually need to resize.
            if ((oldWidths[id] > newWidths[id] && newWidths[id] < embedWidths[id]) || (oldWidths[id] < newWidths[id] && oldWidths[id] < embedWidths[id])) {
              $embedInner.replaceWith('<div class="field__item">' + flickrElements[id] + '</div>');
            }
          }
        });
      }

      $(window).on('resize load', Drupal.debounce(replaceFlickr, 150));
    }
  };

})(jQuery, Drupal);
