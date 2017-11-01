/**
 * @file
 * Image Gallery behaviours.
 *
 */
(function ($, Drupal) {

  'use strict';

  /**
   * A function to get an array of attributes from an element.
   * @param $element
   *   The element to get the attributes from.
   * @returns array
   *   An array of attributes, excluding data-lazy and class
   */
  var getAttributes = function($element) {
    var attrs = {};
    $.each( $element[0].attributes, function (index, attribute) {
      if (attribute.name !== 'data-lazy' && attribute.name !== 'class') {
        attrs[attribute.name] = attribute.value;
      }
    });

    return attrs;
  };

  /**
   * Updates a gallery thumbnail image ready for re-loading the slick slider.
   * @param $element
   *   The <img> element.
   * @param imageUrl
   *   The active image URL.
   */
  var applyGalleryThumb = function($element, imageUrl) {
    var attributes = getAttributes($element);
    var $newImg = $('<img />');
    $newImg.attr(attributes);
    $newImg.attr('data-lazy', imageUrl);
    $element.replaceWith($newImg);
  };

  /**
   * Updates the href of a link for a gallery.
   * @param $element
   *   The <a> element.
   * @param imageUrl
   *   The active image URL.
   */
  var applyGalleryImage = function($element, imageUrl) {
    $element.attr('href', imageUrl);
  };

  var slickOptions = {
    lazyLoad: 'ondemand',
    centerMode: true,
    centerPadding: '230px',
    slidesToShow: 1,
    swipeToSlide: true,
    dots: true,
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          centerMode: true,
          centerPadding: '202px',
          slidesToShow: 1
        }
      },
      {
        breakpoint: 768,
        settings: {
          centerMode: true,
          centerPadding: '143px',
          slidesToShow: 1
        }
      },
      {
        breakpoint: 480,
        settings: {
          centerMode: true,
          centerPadding: '44px',
          slidesToShow: 1
        }
      }
    ]
  };

  /**
   * Behaviour to set up the image gallery.
   *
   * @type {{attach: Drupal.behaviors.wildlifeImageGallery.attach}}
   */
  Drupal.behaviors.wildlifeImageGallery = {
    attach: function (context, settings) {
      var $galleries = $('.field--name-field-gallery-items', context);

      $galleries.once().each(function(i) {
        var $gallery = $(this);

        // Initialize the carousel.
        if ($gallery.find('.field__item').length > 2) {
          $gallery
            .on('init', function(event, slick) {
              slick.$slideTrack.attr('aria-label', 'carousel');
            })
            .slick(slickOptions);

          var slickMaximumSize = false;
          // Get the breakpoints, set default and remove the ones we don't need.
          var slickBreakpoints = $.extend({}, settings.breakpoints);
          delete slickBreakpoints['default'];
          delete slickBreakpoints.desk;
          delete slickBreakpoints.desk_wide;

          $.each(slickBreakpoints, function (label, breakpoint) {
            enquire.register(breakpoint, {
              match : function() {
                // Once we have hit the highest size of image, no need to keep
                // re-loading new items.
                if (slickMaximumSize) {
                  return;
                }

                // Lap is the largest image style we are using.
                if (label === 'lap') {
                  slickMaximumSize = true;
                }

                var $components = $gallery.find('.field__item img');
                // Loop through each image and tell it which image to use, then
                // refresh Slick so it reloads them.
                $components.each(function () {
                  var $component = $(this);
                  applyGalleryThumb($component, $component.data('lazy-responsive-' + label));
                }).promise().done(function(){
                  $gallery.slick('refresh').find('.slick-track').attr('aria-label', 'carousel');
                });
              }
            });
          });
        }

        // Set-up the Gallery pop-ups.
        var $galleryLinks = $gallery.find('.field__item a');

        $galleryLinks.colorbox({
          rel: 'gallery-' + i,
          close: '<span class="colorbox-close__icon" aria-hidden="true" role="presentation"><span class="colorbox-close__icon__line colorbox-close__icon__line--top"></span><span class="colorbox-close__icon__line colorbox-close__icon__line--bottom"></span></span><span class="colorbox-close__text">Close</span>',
          current: false,
          opacity: 0.7,
          maxWidth: '100%',
          maxHeight: '100%',
          onClosed: function() {
            $gallery.slick('refresh').find('.slick-track').attr('aria-label', 'carousel');
          }
        });

        var colorboxBreakpoints = $.extend({}, settings.breakpoints);
        delete colorboxBreakpoints['default'];
        var colorboxMaximumSize = false;

        $.each(colorboxBreakpoints, function (label, breakpoint) {
          enquire.register(breakpoint, {
            match : function() {
              // Once we have hit the highest size of image, no need to keep
              // re-loading new items.
              if (colorboxMaximumSize) {
                return;
              }

              // Lap is the largest image style we are using.
              if (label === 'desk_wide') {
                colorboxMaximumSize = true;
              }

              // Loop through each anchor and tell it which href to use.
              $galleryLinks.each(function () {
                var $link = $(this);
                applyGalleryImage($link, $link.data('full-' + label));
              });
            }
          });
        });
      });
    }
  };

})(jQuery, Drupal);
