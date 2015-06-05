(function( w, undefined ){
	/**
	 * The shoestring object constructor.
	 *
	 * @param {string,object} prim The selector to find or element to wrap.
	 * @param {object} sec The context in which to match the `prim` selector.
	 * @returns shoestring
	 * @this window
	 */
	function shoestring( prim, sec ){
		var pType = typeof( prim ),
				ret = [],
				sel;

		// return an empty shoestring object
		if( !prim ){
			return new Shoestring( ret );
		}

		// ready calls
		if( prim.call ){
			return shoestring.ready( prim );
		}

		// handle re-wrapping shoestring objects
		if( prim.constructor === Shoestring && !sec ){
			return prim;
		}

		// if string starting with <, make html
		if( pType === "string" && prim.indexOf( "<" ) === 0 ){
			var dfrag = document.createElement( "div" );

			dfrag.innerHTML = prim;

			// TODO depends on children (circular)
			return shoestring( dfrag ).children().each(function(){
				dfrag.removeChild( this );
			});
		}

		// if string, it's a selector, use qsa
		if( pType === "string" ){
			if( sec ){
				return shoestring( sec ).find( prim );
			}

				sel = document.querySelectorAll( prim );

			return new Shoestring( sel, prim );
		}

		// array like objects or node lists
		if( Object.prototype.toString.call( pType ) === '[object Array]' ||
				(window.NodeList && prim instanceof window.NodeList) ){

			return new Shoestring( prim, prim );
		}

		// if it's an array, use all the elements
		if( prim.constructor === Array ){
			return new Shoestring( prim, prim );
		}

		// otherwise assume it's an object the we want at an index
		return new Shoestring( [prim], prim );
	}

	var Shoestring = function( ret, prim ) {
		this.length = 0;
		this.selector = prim;
		shoestring.merge(this, ret);
	};

	// TODO only required for tests
	Shoestring.prototype.reverse = [].reverse;

	// For adding element set methods
	shoestring.fn = Shoestring.prototype;

	// expose for testing purposes only
	shoestring.Shoestring = Shoestring;

	// For extending objects
	// TODO move to separate module when we use prototypes
	shoestring.extend = function( first, second ){
		for( var i in second ){
			if( second.hasOwnProperty( i ) ){
				first[ i ] = second[ i ];
			}
		}

		return first;
	};

	// taken directly from jQuery
	shoestring.merge = function( first, second ) {
		var len, j, i;

		len = +second.length,
		j = 0,
		i = first.length;

		for ( ; j < len; j++ ) {
			first[ i++ ] = second[ j ];
		}

		first.length = i;

		return first;
	};

	// expose
	window.shoestring = shoestring;



	var xmlHttp = function() {
		try {
			return new XMLHttpRequest();
		}
		catch( e ){
			return new ActiveXObject( "Microsoft.XMLHTTP" );
		}
	};

	/**
	 * Make an HTTP request to a url.
	 *
	 * **NOTE** the following options are supported:
	 *
	 * - *method* - The HTTP method used with the request. Default: `GET`.
	 * - *data* - Raw object with keys and values to pass with request as query params. Default `null`.
	 * - *headers* - Set of request headers to add. Default `{}`.
	 * - *async* - Whether the opened request is asynchronouse. Default `true`.
	 * - *success* - Callback for successful request and response. Passed the response data.
	 * - *error* - Callback for failed request and response.
	 * - *cancel* - Callback for cancelled request and response.
	 *
	 * @param {string} url The url to request.
	 * @param {object} options The options object, see Notes.
	 * @return shoestring
	 * @this shoestring
	 */

	shoestring.ajax = function( url, options ) {
		var params = "", req = xmlHttp(), settings, key;

		settings = shoestring.extend( {}, shoestring.ajax.settings );

		if( options ){
			shoestring.extend( settings, options );
		}

		if( !url ){
			url = settings.url;
		}

		if( !req || !url ){
			return;
		}

		// create parameter string from data object
		if( settings.data ){
			for( key in settings.data ){
				if( settings.data.hasOwnProperty( key ) ){
					if( params !== "" ){
						params += "&";
					}
					params += encodeURIComponent( key ) + "=" +
						encodeURIComponent( settings.data[key] );
				}
			}
		}

		// append params to url for GET requests
		if( settings.method === "GET" && params ){
			
			url += "?" + params;
		}

		req.open( settings.method, url, settings.async );

		if( req.setRequestHeader ){
			req.setRequestHeader( "X-Requested-With", "XMLHttpRequest" );

			// Set 'Content-type' header for POST requests
			if( settings.method === "POST" && params ){
				req.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
			}

			for( key in settings.headers ){
				if( settings.headers.hasOwnProperty( key ) ){
					req.setRequestHeader(key, settings.headers[ key ]);
				}
			}
		}

		req.onreadystatechange = function () {
			if( req.readyState === 4 ){
				// Trim the whitespace so shoestring('<div>') works
				var res = (req.responseText || '').replace(/^\s+|\s+$/g, '');
				if( req.status.toString().indexOf( "0" ) === 0 ){
					return settings.cancel( res, req.status, req );
				}
				else if ( req.status.toString().match( /^(4|5)/ ) && RegExp.$1 ){
					return settings.error( res, req.status, req );
				}
				else if (settings.success) {
					return settings.success( res, req.status, req );
				}
			}
		};

		if( req.readyState === 4 ){
			return req;
		}

		// Send request
		if( settings.method === "POST" && params ){
			req.send( params );
		} else {
			req.send();
		}

		return req;
	};

	shoestring.ajax.settings = {
		success: function(){},
		error: function(){},
		cancel: function(){},
		method: "GET",
		async: true,
		data: null,
		headers: {}
	};



	/**
	 * Helper function wrapping a call to [ajax](ajax.js.html) using the `GET` method.
	 *
	 * @param {string} url The url to GET from.
	 * @param {function} callback Callback to invoke on success.
	 * @return shoestring
	 * @this shoestring
	 */
	shoestring.get = function( url, callback ){
		return shoestring.ajax( url, { success: callback } );
	};



	/**
	 * Iterates over `shoestring` collections.
	 *
	 * @param {function} callback The callback to be invoked on each element and index
	 * @return shoestring
	 * @this shoestring
	 */
	shoestring.fn.each = function( callback ){
		return shoestring.each( this, callback );
	};

	shoestring.each = function( collection, callback ) {
		var val;
		for( var i = 0, il = collection.length; i < il; i++ ){
			val = callback.call( collection[i], i, collection[i] );
			if( val === false ){
				break;
			}
		}

		return collection;
	};



	var set = function( html ){
		if( typeof html === "string" ){
			return this.each(function(){
				this.innerHTML = html;
			});
		} else {
			var h = "";
			if( typeof html.length !== "undefined" ){
				for( var i = 0, l = html.length; i < l; i++ ){
					h += html[i].outerHTML;
				}
			} else {
				h = html.outerHTML;
			}
			return this.each(function(){
				this.innerHTML = h;
			});
		}
	};
	/**
	 * Gets or sets the `innerHTML` from all the elements in the set.
	 *
	 * @param {string|undefined} html The html to assign
	 * @return {string|shoestring}
	 * @this shoestring
	 */
	shoestring.fn.html = function( html ){
				if( typeof html !== "undefined" ){
			return set.call( this, html );
		} else { // get
			var pile = "";

			this.each(function(){
				pile += this.innerHTML;
			});

			return pile;
		}
	};



})( this );