/**
 * @file
 * Behavior for the Link Widget in CKEditors.
 */
(function($, Drupal) {
  "use strict";
  Drupal.behaviors.wildlifeAdminLinkWidget = {
    attach: function(context) {
      // When the Email field is checked, prepend mailto: to the URL field.
      var $emailCheckbox = $('input[name="attributes[data-email]"]', context);
      var $urlField = $emailCheckbox.parents('form').find('input[name="attributes[href]"]');

      $emailCheckbox.once().change(function (){
        var $checkbox = $(this);
        var checked = $checkbox.is(":checked");
        var urlValue = $urlField.val();
        var newUrlValue = false;

        if (checked && (urlValue.match('^mailto:') == null)) {
          newUrlValue = 'mailto:' + urlValue;
        }
        else if (!checked) {
          newUrlValue = urlValue.replace('mailto:', '');
        }

        if (newUrlValue !== false) {
          $urlField.val(newUrlValue);
        }
      });
    }
  };
})(jQuery, Drupal);
