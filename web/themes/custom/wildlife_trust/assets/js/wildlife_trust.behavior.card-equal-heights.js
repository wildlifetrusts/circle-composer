/**
 * @file
 * Equal heights for Card view modes behaviour.
 *
 */
(function ($, Drupal) {
  Drupal.behaviors.cardsEqualHeights = {
    attach: function (context) {
      $('.card .card__details', context).matchHeight();
    }
  };
})(jQuery, Drupal);
