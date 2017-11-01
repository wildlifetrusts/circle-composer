/**
 * @file
 * Global search toggle.
 *
 */
(function ($, Drupal) {
  'use strict';

  /**
   * Behaviour to add the search toggle button and affect a toggle on click.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustGlobalSearchToggle.attach}}
   */
  Drupal.behaviors.wildlifeTrustGlobalSearchToggle = {
    attach: function (context) {
      var $body = $('body', context);
      var $button = $('<button />', context);
      var toggleClass = 'is--search-open';
      var activeButtonClass = 'is--search-active';
      $button.addClass('search-toggle');
      $button.append('<span aria-hidden="true" class="search-toggle__icon" role="presentation" />');
      $button.find('.search-toggle__icon').append('<span class="search-toggle__icon__line search-toggle__icon__line--top"></span><span class="search-toggle__icon__line search-toggle__icon__line--bottom"></span>');
      $button.append('<span class="search-toggle__text">Search</span>');

      $('.l-top-bar').once().prepend($button);

      $button.click(function() {
        $body.toggleClass(toggleClass);
        $(this).toggleClass(activeButtonClass);
      });
    }
  };

})(jQuery, Drupal);
