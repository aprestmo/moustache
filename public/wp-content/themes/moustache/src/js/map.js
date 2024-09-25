// Dynamically load the Google Maps API script
if (typeof googleMapsApiKey !== 'undefined') {
  const script = document.createElement('script');
  script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&callback=initMaps&libraries=marker`; // Add libraries=marker to load the advanced marker
  script.async = true; // Ensures non-blocking load
  script.defer = true; // Ensures execution after parsing
  script.setAttribute('loading', 'async'); // Best practice loading pattern
  document.head.appendChild(script);
}

// Callback function that initializes the maps after the API script loads
window.initMaps = function () {
  document.querySelectorAll('.acf-map').forEach((mapElement) => {
    initMap(mapElement);
  });
};

/**
 * initMap
 *
 * Renders a Google Map onto the selected element
 *
 * @param {HTMLElement} el The element.
 * @return {Object} The map instance.
 */
function initMap(el) {
  // Find marker elements within the map.
  const markers = el.querySelectorAll('.marker');

  // Create a generic map with Map ID.
  const mapArgs = {
    zoom: el.dataset.zoom ? parseInt(el.dataset.zoom) : 16,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    mapId: 'ca2e126df65f33a4', // Replace with your actual Map ID from Google Cloud Console
  };
  const map = new google.maps.Map(el, mapArgs);

  // Add markers.
  map.markers = [];
  markers.forEach((marker) => {
    initMarker(marker, map);
  });

  // Center the map based on markers.
  centerMap(map);

  return map;
}

/**
 * initMarker
 *
 * Creates a marker for the given element and map.
 *
 * @param {HTMLElement} marker The marker element.
 * @param {Object} map The map instance.
 * @return {Object} The marker instance.
 */
function initMarker(marker, map) {
  // Get position from marker.
  const lat = marker.dataset.lat;
  const lng = marker.dataset.lng;
  const latLng = {
    lat: parseFloat(lat),
    lng: parseFloat(lng),
  };

  // Create marker instance using AdvancedMarkerElement.
  const advancedMarker = new google.maps.marker.AdvancedMarkerElement({
    map: map,
    position: latLng,
    title: marker.dataset.title || '', // Optional: Add a title or other properties if needed
  });

  // Append to reference for later use.
  map.markers.push(advancedMarker);

  // If marker contains HTML, add it to an infoWindow.
  if (marker.innerHTML.trim() !== '') {
    // Create info window.
    const infowindow = new google.maps.InfoWindow({
      content: marker.innerHTML,
    });

    // Show info window when marker is clicked.
    advancedMarker.addListener('gmp-click', () => {
      infowindow.open(map, advancedMarker);
    });
  }
}

/**
 * centerMap
 *
 * Centers the map showing all markers in view.
 *
 * @param {Object} map The map instance.
 * @return {void}
 */
function centerMap(map) {
  // Create map boundaries from all map markers.
  const bounds = new google.maps.LatLngBounds();
  map.markers.forEach((marker) => {
    bounds.extend(marker.position);
  });

  // Case: Single marker.
  if (map.markers.length === 1) {
    map.setCenter(bounds.getCenter());
  } else {
    // Case: Multiple markers.
    map.fitBounds(bounds);
  }
}

// Initialize maps on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  // The map will be initialized by the callback `initMaps` after Google Maps API script is loaded
});
