/*!
 Ridiculously Responsive Social Sharing Buttons Plus
 */

(function(window, $) {
  'use strict';
  var schema = {
    size: {min: 0.1, max: 10, default: 1},
    shrink: {min: 0.2, max: 1, default: 0.8},
    regrow: {min: 0.2, max: 1, default: 0.8},
    minRows: {min: 1, max: 99, default: 1},
    maxRows: {min: 1, max: 99, default: 2},
    prefixReserve: {min: 0, max: 0.8, default: 0.3},
    prefixHide: {min: 0.1, max: 10, default: 2},
  };

  /**
   * Public function to configure all sets of buttons on the page.
   */
  window.rrssbConfigAll = function(settings) {
    $('.rrssb').each(function(){
      $(this).rrssbConfig(settings);
    });
  };

  /**
   * Public function to configure the set of buttons.
   * $(this) points to an instance of .rrssb
   */
  $.fn.rrssbConfig = function(settings) {
    if ($(this).data('settings') && !settings) {
      return;
    }

    var checkedSettings = {};
    for (var param in schema) {
      if (settings && !isNaN(parseFloat(settings[param]))) {
        checkedSettings[param] = Math.min(schema[param].max, Math.max(schema[param].min, settings[param]));
      }
      else {
        checkedSettings[param] = schema[param].default;
      }
    }

    $(this).data('settings', checkedSettings);
  };

  var popupCenter = function(url, title, w, h) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 3) - (h / 3)) + dualScreenTop;

    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (newWindow && newWindow.focus) {
      newWindow.focus();
    }
  };

  /**
   * Ready function
   */
  $(document).ready(function() {
    // Register event listeners
    $('.rrssb-buttons a.popup').click(function popUp(e) {
      popupCenter($(this).attr('href'), $(this).find('.rrssb-text').html(), 580, 470);
      e.preventDefault();
    });

    // Add another ready callback that will be called after all others.
    // Configure any buttons that haven't already been configured.
    $(document).ready(function() { rrssbConfigAll(); });
  });

})(window, jQuery);
