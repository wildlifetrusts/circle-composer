/**
 * @file
 * RRSSB Sticky behaviours.
 *
 */
(function ($, Drupal) {
  'use strict';

  /**
   * @type {{attach: Drupal.behaviors.rrssbSticky.attach}}
   */
  Drupal.behaviors.rrssbSticky = {
    attach: function (context) {
      var $main = $('.l-main', context);
      var $socialShareBlock = $('.block--rrssb', context);
      var $nodeHeader = $('.node__header', context);
      var rrssbStickyTopWaypoint = false;
      var rrssbStickyBottomWaypoint = false;

      enquire.register('all and (max-width:479px), all and (max-height:465px)', {
        match : function() {
          $socialShareBlock.addClass('is-horizontal');

          if ($nodeHeader.length) {
            $socialShareBlock.addClass('is-hidden');

            rrssbStickyTopWaypoint = new Waypoint({
              element: $nodeHeader,
              handler: function(direction) {
                if (direction === 'up') {
                  $socialShareBlock.addClass('is-hidden');
                }
                else {
                  $socialShareBlock.removeClass('is-hidden');
                }
              },
              offset: function () {
                return (this.context.innerHeight() - this.adapter.outerHeight()) - 48;
              }
            });
          }

          rrssbStickyBottomWaypoint = new Waypoint({
            element: $main,
            handler: function(direction) {
              if (direction === 'down') {
                $socialShareBlock.addClass('is-hidden');
              }
              else {
                $socialShareBlock.removeClass('is-hidden');
              }
            },
            offset: 'bottom-in-view'
          });
        },
        unmatch : function() {
          $socialShareBlock.removeClass('is-horizontal').removeClass('is-hidden');

          if (rrssbStickyTopWaypoint) {
            rrssbStickyTopWaypoint.destroy();
          }

          if (rrssbStickyBottomWaypoint) {
            rrssbStickyBottomWaypoint.destroy();
          }
        }
      });
    }
  };
})(jQuery, Drupal);
