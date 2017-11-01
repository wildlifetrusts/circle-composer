(function ($) {

  /**
   * Behaviour to convert a field with the standard Drupal mark-up and an
   * added 'slick-field' class into a Slick slider.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustSlickFieldDefault.attach}}
   */
  Drupal.behaviors.wildlifeTrustSlickFieldDefault = {
    attach: function () {
      var options = {
        dots: true,
        draggable: false,
        arrows: false,
        mobileFirst: true,
        infinite: true
      };

      var $slickField = $('.slick-field');

      if ($slickField.find('.field__item').length > 1) {
        $('.slick-field')
          .on('init', function(event, slick) {
            slick.$slideTrack.attr('aria-label', 'carousel');
          })
          .slick(options);
      }
    }
  };

})(jQuery);
