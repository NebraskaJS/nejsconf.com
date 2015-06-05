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

	if( "JSON" in window ) {
		$.get( "/tickets-remaining", function( data ) {
			var json = JSON.parse( data );
			if( json ) {
				var left = json.tickets;
				if( !isNaN( left ) ) {
					$( '#tickets-left' ).html( left + ' Ticket' + ( left !== 1 ? 's' : '' ) + ' Left at this Price!' );
				}
			}
		});
	}

})( document, shoestring );