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

	$.get( "/tickets-sold?rand=" + Math.random(), function( data ) {
		var sold = parseInt( data, 10 );
		if( !isNaN( sold ) ) {
			var left = 75 - sold;
			$( '#tickets-left' ).html( left + ' Ticket' + ( left !== 1 ? 's' : '' ) + ' Left at this Price!' );
		}
	});
})( document, shoestring );