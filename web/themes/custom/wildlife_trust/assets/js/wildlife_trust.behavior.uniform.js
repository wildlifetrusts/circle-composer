/**
 * @file
 * Uniform form element behaviour.
 *
 */
(function ($, Drupal) {
  Drupal.behaviors.uniform = {
    attach: function (context) {
      $(context).find('select, [type="file"]').not('.js-no-uniform').uniform({
        selectClass: 'form__item--type-uniform-select',
        fileClass: 'form__item--type-uniform-file',
        filenameClass: 'uniform-file__filename',
        fileButtonClass: 'uniform-file__action button button--small button--soft-end',
        disabledClass: 'is-disabled',
        hoverClass: 'is-hover',
        focusClass: 'is-focus'
      });
    }
  };
})(jQuery, Drupal);
