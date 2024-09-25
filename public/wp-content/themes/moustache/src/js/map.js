(function ($) {
  // Dynamically load the Google Maps API script
  if (typeof googleMapsApiKey !== 'undefined') {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&callback=initMaps`; // Add the callback parameter
    script.async = true;
    document.head.appendChild(script);
  }

  // Callback function that initializes the maps after the API script loads
  window.initMaps = function () {
    $('.acf-map').each(function () {
      initMap($(this));
    });
  };

  /**
   * initMap
   *
   * Renders a Google Map onto the selected jQuery element
   *
   * @param jQuery $el The jQuery element.
   * @return object The map instance.
   */
  function initMap($el) {
    var $markers = $el.find('.marker');

    // Create generic map.
    var mapArgs = {
      zoom: $el.data('zoom') || 16,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    var map = new google.maps.Map($el[0], mapArgs);

    // Add markers.
    map.markers = [];
    $markers.each(function () {
      initMarker($(this), map);
    });

    // Center map based on markers.
    centerMap(map);

    return map;
  }

  function initMarker($marker, map) {
    var lat = $marker.data('lat');
    var lng = $marker.data('lng');
    var latLng = {
      lat: parseFloat(lat),
      lng: parseFloat(lng),
    };

    var marker = new google.maps.Marker({
      position: latLng,
      map: map,
    });

    map.markers.push(marker);

    if ($marker.html()) {
      var infowindow = new google.maps.InfoWindow({
        content: $marker.html(),
      });

      google.maps.event.addListener(marker, 'click', function () {
        infowindow.open(map, marker);
      });
    }
  }

  function centerMap(map) {
    var bounds = new google.maps.LatLngBounds();
    map.markers.forEach(function (marker) {
      bounds.extend({
        lat: marker.position.lat(),
        lng: marker.position.lng(),
      });
    });

    if (map.markers.length == 1) {
      map.setCenter(bounds.getCenter());
    } else {
      map.fitBounds(bounds);
    }
  }
})(jQuery);
