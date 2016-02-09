;(function( doc, $ ) {
	// IE9+
	if( !( 'geolocation' in navigator ) ) {
		return;
	}
	if( location.hash === "#post" && "replaceState" in history ) {
		window.setTimeout(function() {
			history.replaceState( "", doc.title, window.location.pathname );
		}, 200);
	}

	$( '#venue-photo' ).html( '<img src="/assets/img/venue.jpg" alt="Panorama of the Zoo Venue">' );
	$( '#hotel-map' ).html( '<img src="https://maps.googleapis.com/maps/api/staticmap?center=101+S+10th+St+Omaha,+NE+68102&zoom=13&scale=2&size=300x200&maptype=roadmap&format=png&visual_refresh=true&markers=size:mid%7Ccolor:red%7Clabel:1%7C101+S+10th+St+Omaha,+NE+68102" alt="Google Map of Hotel">' );
	$( '#venue-map' ).html( '<img src="https://maps.googleapis.com/maps/api/staticmap?center=3701+S+10th+St+Omaha,+NE+68107&zoom=13&scale=2&size=300x200&maptype=roadmap&format=png&visual_refresh=true&markers=size:mid%7Ccolor:red%7Clabel:2%7C3701+S+10th+St+Omaha,+NE+68107" alt="Google Map of the Zoo">' );

})( document, shoestring );