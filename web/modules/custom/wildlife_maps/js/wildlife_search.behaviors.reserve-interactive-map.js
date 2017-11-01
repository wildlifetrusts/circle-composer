(function($, Drupal) {
  'use strict';

  /** The path to the icons. */
  var iconBase = '/modules/custom/wildlife_maps/markers/map-marker_';

  /**
   * Behaviour to set up and initialise Google Map view results.
   *
   * @type {{attach: Drupal.behaviors.locationSearchMap.attach}}
   */
  Drupal.behaviors.locationSearchMap = {
    attach: function(context, settings) {
      var $mapContainer = $('.reserve-interactive-map > div', context);
      var apiKey = settings.wildlife_maps.api_key;
      var interactiveMapSettings = settings.wildlife_maps.interactive_map;

      var mapOptions = {
        zoom: parseInt(interactiveMapSettings.zoom, 10),
        center: {lat: interactiveMapSettings.lat, lng: interactiveMapSettings.lng},
        scrollwheel: false,
        maxZoom: 15,
        mapTypeId: interactiveMapSettings.layer,
        mapTypeControlOptions: {
          mapTypeIds: ['roadmap', 'satellite', 'terrain', 'hybrid']
        }
      };

      // Initialise the Google Map.
      function initMap() {
        var map = new google.maps.Map($mapContainer[0], mapOptions);
        var bounds = false;

        var reserveIcon = {
          url: iconBase + 'reserve.png',
          size: new google.maps.Size(48, 56),
          anchor: new google.maps.Point(24, 48)
        };

        var reserveMarker = new google.maps.Marker({
          map: map,
          position: {
            lat: interactiveMapSettings.lat,
            lng: interactiveMapSettings.lng
          },
          icon: reserveIcon,
          title: $('h1.node__title').text()
        });

        if (interactiveMapSettings.kml) {
          var reserveBoundary = new google.maps.KmlLayer({
            url: interactiveMapSettings.kml,
            map: map
          });

          google.maps.event.addListener(reserveBoundary, 'defaultviewport_changed', function() {
            bounds = reserveBoundary.getDefaultViewport();
            bounds.extend(reserveMarker.getPosition());
            map.fitBounds(bounds);
          });
        }

        // Update the center when the page is resized.
        var initialBounds = true;
        var mapCenter = false;
        var mapInteractedWith = false;

        map.addListener('bounds_changed', function() {
          if (initialBounds) {
            mapCenter = map.getCenter();
            initialBounds = false;
          }
        });

        map.addListener('dragend', function() {
          mapInteractedWith = true;
          mapCenter = map.getCenter();
        });

        map.addListener('zoom_changed', function() {
          mapInteractedWith = true;
          mapCenter = map.getCenter();
        });

        $(window).on('resize', Drupal.debounce(function() {
          if (!mapInteractedWith) {
            if (interactiveMapSettings.kml && bounds) {
              map.fitBounds(bounds);
            }
            else {
              map.setCenter(mapCenter);
            }
          }
          else if (mapCenter) {
            map.setCenter(mapCenter);
            mapCenter = map.getCenter();
          }
        }, 150));
      }

      // Check for google maps.
      if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        var scriptPath = '//maps.googleapis.com/maps/api/js?key=' + apiKey;

        $.getScript(scriptPath)
          .done(function () {
            initMap();
          });
      }
      else {
        initMap();
      }
    }
  };
})(jQuery, Drupal);
