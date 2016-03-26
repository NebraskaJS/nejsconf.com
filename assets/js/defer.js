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

	// if( "JSON" in window ) {
	// 	$.get( "/tickets-remaining", function( data ) {
	// 		var json = JSON.parse( data );
	// 		if( json ) {
	// 			var left = json.tickets;
	// 			if( !isNaN( left ) ) {
	// 				left = Math.max( 0, left );
	// 				if( left <= 30 ) {
	// 					$( '#tickets-left' ).html( left + ' Ticket' + ( left !== 1 ? 's' : '' ) + ' Left!' );
	// 				}
	// 			}
	// 		}
	// 	});
	// }

})( document, shoestring );