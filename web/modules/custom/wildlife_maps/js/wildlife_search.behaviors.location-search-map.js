(function($, Drupal) {
  'use strict';

  /** Loading InfoWindow content. */
  var loadingContent = '<img src="/themes/custom/wildlife_trust/images/load-spinner.gif" width="32px" height="32px" alt="Loading..." title="Loading..." />';

  /** The path to the icons. */
  var iconBase = '/modules/custom/wildlife_maps/markers/map-marker_';

  /**
   * Cluster marker styles.
   */
  var markerClusterStyles = [{
    url: iconBase + 'cluster-round.png',
    height: 54,
    width: 52,
    textColor: '#fff',
    textSize: 13
  }];

  var mapOptions = {
    zoom: 3,
    center: {lat: 54.345341, lng: -2.776943},
    scrollwheel: false,
    maxZoom: 15,
    mapTypeId: 'roadmap',
    mapTypeControlOptions: {
      mapTypeIds: ['roadmap', 'satellite', 'terrain', 'hybrid']
    }
  };

  /**
   * Behaviour to set up and initialise Google Map view results.
   *
   * @type {{attach: Drupal.behaviors.locationSearchMap.attach}}
   */
  Drupal.behaviors.locationSearchMap = {
    attach: function(context, settings) {
      var $mapContainer = $('.map-view-display', context);
      var apiKey = settings.wildlife_maps.api_key;

      // Load in location data in to the info window.
      function loadMapNodeInfo(location, infoWindow, pin, map) {
        $.ajax({
          type: 'GET',
          url: '/map-ajax/' + location.uuid + '/' + location.type
        }).done(function(data) {
          if (location.distance) {
            var $contents = $(data);
            $contents.wrap('<div class="container" />');
            $contents.find('.node__title').before(location.distance);
            infoWindow.setContent($contents.parent().html());
          }
          else {
            infoWindow.setContent(data);
          }

          infoWindow.open(map, pin);
        });
      }

      // Initialise the Google Map.
      function initMap() {
        // Marker icon types.
        var icons = {
          reserve: {
            url: iconBase + 'reserve.png',
            size: new google.maps.Size(48, 56),
            anchor: new google.maps.Point(25, 48)
          },
          event: {
            url: iconBase + 'event.png',
            size: new google.maps.Size(48, 56),
            anchor: new google.maps.Point(25, 48)
          },
          location_cluster: {
            url: iconBase + 'cluster-pointer.png',
            size: new google.maps.Size(48, 66),
            anchor: new google.maps.Point(25, 59)
          }
        };

        // Set up the map element, and hide or show based on locations.
        var $mapElement = $mapContainer.find('#results-map');

        if (!locations.length) {
          $mapElement.hide();
          return;
        }
        else {
          $mapElement.show();
        }

        var map = new google.maps.Map($mapElement[0], mapOptions);
        var bounds = new google.maps.LatLngBounds();

        var infoWindow = new google.maps.InfoWindow({
          content: loadingContent
        });

        // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
        var pins = [];
        var markers = locations.map(function(location, i) {
          pins[i] = new google.maps.Marker({
            position: location.position,
            icon: icons[location.type],
            title: location.title,
            map: map,
            anchorPoint: new google.maps.Point(-1, -44)
          });

          if (location.type === 'location_cluster') {
            var locationData = location.data;
            var locationCount = Object.keys(locationData).length;
            pins[i].setLabel({
              text: locationCount.toString(),
              color: '#fff',
              fontSize: '13px'
            });
          }

          bounds.extend(pins[i].getPosition());

          pins[i].addListener('click', function() {
            if (infoWindow) {
              infoWindow.close();
              infoWindow.setContent(loadingContent);
            }
            infoWindow.open(map, pins[i]);

            // Location "clusters" where multiple events happen at one location
            // need to be processed so that they show a list of all the events
            // on click.
            if (location.type === 'location_cluster') {
              var $clustererInfo = $('<div class="cluster-info" />');
              $clustererInfo.append('<h2 class="cluster-info__heading">' + Drupal.t('There are @count results at this exact location:', {'@count': locationCount}) + '</h2>');
              $clustererInfo.append('<ul class="cluster-info__list" />');

              $.each(locationData, function(markerInfoIndex) {
                $clustererInfo.find('ul').append('<li><a href="#" class="cluster-item-opener" data-location-cluster-key="' + i + '" data-location-key="' + markerInfoIndex + '">' + this.title + '</a></li>');
              });

              infoWindow.setContent($clustererInfo.html());
              infoWindow.open(map, pins[i]);


              google.maps.event.addDomListener(infoWindow, 'domready', function() {
                $('.cluster-item-opener').click(function(e) {
                  e.preventDefault();
                  var locationClusterKey = $(this).data('location-cluster-key');
                  var locationDataKey = $(this).data('location-key');

                  infoWindow.setContent(loadingContent);
                  loadMapNodeInfo(locations[locationClusterKey]['data'][locationDataKey], infoWindow, pins[i],  map);
                });
              });
            }
            else {
              loadMapNodeInfo(location, infoWindow, pins[i], map);
            }
          });

          return pins[i];
        });

        // Fit the map to the bounds.
        map.fitBounds(bounds);

        // Add a marker clusterer to manage the markers.
        var markerCluster = new MarkerClusterer(map, markers, {
          styles: markerClusterStyles,
          averageCenter: true
        });

        // Add a new calculator that takes in to account events at a single
        // location pointer.
        markerCluster.setCalculator(function (markers, numStyles) {
            var index = 0;
            var count = 0;

            $.each(markers, function(markerIndex, markerValue) {
              if (markerValue.hasOwnProperty('label')) {
                var singleLocationEventCount = parseFloat(markerValue.label.text);
                count = count + singleLocationEventCount;
              }
              else {
                count++;
              }
            });

            var dv = count;
            while (dv !== 0) {
              dv = parseInt(dv / 10, 10);
              index++;
            }

            index = Math.min(index, numStyles);
            return {
              text: count,
              index: index
            };
        });

        // Update the center when the page is resized.
        var initialBounds = true;
        var mapCenter = false;

        map.addListener('bounds_changed', function() {
          if (initialBounds) {
            mapCenter = map.getCenter();
            initialBounds = false;
          }
        });

        map.addListener('dragend', function() {
          mapCenter = map.getCenter();
        });

        map.addListener('zoom_changed', function() {
          mapCenter = map.getCenter();
        });

        $(window).on('resize', Drupal.debounce(function() {
          if (mapCenter) {
            map.setCenter(mapCenter);
            mapCenter = map.getCenter();
          }
        }, 150));
      }

      // Set up the locations array.
      var preliminaryLocations = [];
      var locations = [];

      // If there is a specific map data region, use information from that,
      // otherwise just compile the map from existing view results.
      var $dataRows = false;

      if ($mapContainer.find('.map__data').length) {
        $dataRows = $mapContainer.find('.map__data .map-data__item');
      }
      else {
        $dataRows = $mapContainer.parent().find('.views-row .node');
      }

      if ($dataRows) {
        // First, check for duplicates and create those markers.
        var sameLocationRows = {};

        // Then, create the normal markers.
        $dataRows.each(function (i) {
          var $data = $(this);
          var lat = $data.data('lat');
          var lng = $data.data('lng');
          var $distance = $data.parent().find('.location-proximity').length ? $data.parent().find('.location-proximity').clone() : false;

          // Only push a location if we have a latitude & longitude.
          if (lat && lng) {
            var locationData = {
              id: i,
              type: $data.data('type'),
              uuid: $data.data('uuid'),
              title: $data.data('title'),
              distance: $distance,
              position: {
                lat: lat,
                lng: lng
              }
            };

            preliminaryLocations.push(locationData);
            var latlng = lat + ',' + lng;

            if (sameLocationRows.hasOwnProperty(latlng)) {
              sameLocationRows[latlng][i] = locationData;
            }
            else {
              sameLocationRows[latlng] = {};
              sameLocationRows[latlng][i] = locationData;
            }
          }
        }).promise().done(function () {
          // Process nodes which exist at the same locations.
          $.each(sameLocationRows, function (latlng, value) {
            var locationKeys = Object.keys(value);
            if (locationKeys.length > 1) {
              $.each(locationKeys, function (i, key) {
                delete preliminaryLocations[key];
              });

              var latlngSplit = latlng.split(',');

              preliminaryLocations.push({
                id: 'cluster_' + latlngSplit[0] + '_' + latlngSplit[1],
                type: 'location_cluster',
                data: value,
                title: Drupal.t('@count results at this exact location.', {'@count': locationKeys.length}),
                position: {
                  lat: parseFloat(latlngSplit[0]),
                  lng: parseFloat(latlngSplit[1])
                }
              });
            }
          });

          // Re-index the locations so they have the correct keys.
          $.each(preliminaryLocations, function (i, value) {
            if (value instanceof Object) {
              locations.push(value);
            }
          });

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
        });
      }
    }
  };
})(jQuery, Drupal);
