/**
 * @file
 * Admin behaviours.
 *
 */
(function ($, Drupal) {
  'use strict';

  /**
   * Behaviour to reload map when a tab with a map in is revealed.
   *
   * @type {{attach: Drupal.behaviors.adminMapTabs.attach}}
   */
  Drupal.behaviors.adminMapTabs = {
    attach: function (context) {
      // If there are vertical tabs the widget should refresh when swapping between them.
      if ($('.vertical-tabs').length > 0 && $('.vertical-tabs__panes').length > 0) {
        var refresh = function () {
          var geofieldMap = Drupal.geofieldMap;
          if (geofieldMap !== undefined) {
            $.each(geofieldMap.map_data, function (mapid) {
              if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                var map_data = geofieldMap.map_data[mapid];
                var map = map_data.map;
                var latlng = {lat: map_data.position.lat(), lng: map_data.position.lng()};
                google.maps.event.trigger(map, 'resize');
                map.setCenter(latlng);
              }
            });
          }
        };

        // Refresh current vertical tab.
        refresh();

        // Refresh when changing to a different vertical tab.
        $('.vertical-tabs__menu', context).find('.vertical-tabs__menu-item').each(function (key, tab) {
          $(tab).find('a').bind('click', refresh);
        });
      }
    }
  };

  /**
   * Behaviour to set default end date when start date is set.
   *
   * @type {{attach: Drupal.behaviors.adminEventDefaultEndDate.attach}}
   */
  Drupal.behaviors.adminEventDefaultEndDate = {
    attach: function (context) {
      var initialSet = false;

      $('#edit-field-event-date-0-value-date', context).once().blur(function () {
        if (!initialSet) {
          var fieldValue = $(this).val();
          var splitValues = fieldValue.split('-');

          // Event dates should only be in the future but use 2000 as a cut off just in case.
          // This is mostly to avoid updating the end date before the user has entered a full
          // year.
          var yearValid = splitValues[0] > 2000;
          var monthValid = splitValues[1] > 0 && splitValues[1] < 13;
          var dateValid = splitValues[2] > 0 && splitValues[2] < 32;
          if (yearValid && monthValid && dateValid) {
            $('#edit-field-event-date-0-end-value-date', context).val(fieldValue);
            initialSet = true;
          }
        }
      });
    }
  };

  /**
   * Behaviour to set Component select back to 'Choose component' when clicked.
   *
   * @type {{attach: Drupal.behaviors.adminComponentSelectReset.attach}}
   */
  Drupal.behaviors.adminComponentSelectReset = {
    attach: function (context) {
      var $addWrapper = $('.form__paragraphs-add', context);

      $addWrapper.each(function() {
        var $wrapper = $(this);
        var $addSelect = $wrapper.find('> .form-type-select .form-select');
        var $addButton = $wrapper.find('.paragraphs-add__action .form-submit');
        var emptyVal = '_none';

        $addSelect.val(emptyVal);
        $addButton.prop('disabled', true);

        $addSelect.change(function () {
          var $select = $(this);

          if ($select.val() === emptyVal) {
            $addButton.prop('disabled', true);
          }
          else {
            $addButton.prop('disabled', false);
          }
        });
      });
    }
  };
})(jQuery, Drupal);
