;(function( doc ) {
	// IE9+
	if( !( 'geolocation' in navigator ) ) {
		return;
	}
	if( location.hash === "#post" && "replaceState" in history ) {
		window.setTimeout(function() {
			history.replaceState( "", doc.title, window.location.pathname );
		}, 200);
	}

	var venuePhoto = document.getElementById( 'venue-photo' );
	if( venuePhoto ) {
		venuePhoto.innerHTML = '<img src="/assets/img/venue.jpg" alt="Panorama of the Zoo Venue">';
	}
})( document );