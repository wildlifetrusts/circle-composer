/**
 * @file
 * Author behaviours.
 *
 */
(function ($, Drupal) {
  'use strict';

  /**
   * Behaviour to toggle the biography field.
   *
   * @type {{attach: Drupal.behaviors.author.attach}}
   */
  Drupal.behaviors.author = {
    attach: function (context) {
      var $author = $('.author', context);

      if ($author.find('.field--name-field-author-bio').length) {
        var $authorMain = $author.find('.author__main > .field-group-wrapper');
        var toggleClass = 'toggle--bio';
        var toggleText = 'Show biography';

        $authorMain.append('<a class="' + toggleClass + '">' + toggleText + '</a>').find('.' + toggleClass).click(function () {
          var $toggle = $(this);
          $toggle.closest('.author').toggleClass('bio--is-expanded');
        });
      }
    }
  };
})(jQuery, Drupal);
