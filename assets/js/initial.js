var NEJSConf = {
	getDistFolder: function() {
		var distMeta = document.querySelector( 'meta[name="dist"]' );

		return distMeta ? distMeta.content : '';
	}
};

// TODO import this using npm and grunt
/* grunticon Stylesheet Loader | https://github.com/filamentgroup/grunticon | (c) 2012 Scott Jehl, Filament Group, Inc. | MIT license. */
;(function(e){"use strict";var t=e.document,n=e.navigator,a=e.Image,r=!(!t.createElementNS||!t.createElementNS("http://www.w3.org/2000/svg","svg").createSVGRect||!t.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image","1.1")||e.opera&&-1===n.userAgent.indexOf("Chrome")||-1!==n.userAgent.indexOf("Series40")),o=function(n,a){a=a||function(){};var r=t.createElement("link"),o=t.getElementsByTagName("script")[0];r.rel="stylesheet",r.href=n,r.media="only x",r.onload=a,o.parentNode.insertBefore(r,o),e.setTimeout(function(){r.media="all"})},i=function(e,n){if(e&&3===e.length){var A=new a;A.onerror=function(){i.method="png",o(e[2])},A.onload=function(){var t=1===A.width&&1===A.height,a=e[t&&r?0:t?1:2];i.method=t&&r?"svg":t?"datapng":"png",i.href=a,o(a,n)},A.src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==",t.documentElement.className=t.documentElement.className+" grunticon"}};i.loadCSS=o,e.grunticon=i})(this);

;(function( doc ) {
	// IE9+
	if( !( 'geolocation' in navigator ) ) {
		return;
	}

	var templateName = doc.querySelector( 'meta[name="template"]' );
	document.documentElement.className += ' enhanced-js' +
		( templateName ? " tmpl-" + templateName.content : "" ) +
		// gradient inference
		( 'matchMedia' in window ? " has-gradient" : "" );
})( document );

// Grunticon
;(function( doc ) {
	// IE8+
	if( !( 'querySelector' in doc ) ) {
		return;
	}

	var distFolder = NEJSConf.getDistFolder();

	grunticon( [ distFolder + "icons/icons.data.svg.css",
		distFolder + "icons/icons.data.png.css",
		distFolder + "icons/icons.fallback.css" ] );

})( document );

// TypeKit
;(function( d ) {
	function TypeKitHelper( kitId, callback ) {
		var config = {
				kitId: kitId, // Modification to stock TypeKit loader
				scriptTimeout: 10000
			},
			h=d.documentElement,
			t=setTimeout(function(){
				h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";
			},config.scriptTimeout),
			tk=d.createElement("script"),
			f=false,
			s=d.getElementsByTagName("script")[0],
			a;
		h.className+=" wf-loading";
		tk.src='//use.typekit.net/'+config.kitId+'.js';
		tk.async=true;
		tk.onload=tk.onreadystatechange=function(){
			a=this.readyState;
			if(f||a&&a!="complete"&&a!="loaded")return;
			f=true;
			clearTimeout(t);
			(callback || function() {})(); // Modification to stock TypeKit loader
			try{Typekit.load(config);}catch(e){}
		};
		s.parentNode.insertBefore(tk,s);
	}

	// Starts with Heavy "effra-n9"
	TypeKitHelper( 'myi3gat', function() {
		// Defers with Heavy Italic "effra-i9", Bold "effra-n7", and Medium "effra-n5"
		TypeKitHelper( 'wvu3pqt' );
	});
})( document );