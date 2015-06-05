var NEJSConf = {
	getDistFolder: function() {
		var distMeta = document.querySelector( 'meta[name="dist"]' );

		return distMeta ? distMeta.content : '';
	}
};

// TODO import this using npm and grunt
/*! grunt-grunticon Stylesheet Loader - v2.0.2 | https://github.com/filamentgroup/grunticon | (c) 2015 Scott Jehl, Filament Group, Inc. | MIT license. */
(function(e){function t(t,n,r,o){"use strict";function a(){for(var e,n=0;u.length>n;n++)u[n].href&&u[n].href.indexOf(t)>-1&&(e=!0);e?i.media=r||"all":setTimeout(a)}var i=e.document.createElement("link"),c=n||e.document.getElementsByTagName("script")[0],u=e.document.styleSheets;return i.rel="stylesheet",i.href=t,i.media="only x",i.onload=o||function(){},c.parentNode.insertBefore(i,c),a(),i}var n=function(r,o){"use strict";if(r&&3===r.length){var a=e.navigator,i=e.Image,c=!(!document.createElementNS||!document.createElementNS("http://www.w3.org/2000/svg","svg").createSVGRect||!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image","1.1")||e.opera&&-1===a.userAgent.indexOf("Chrome")||-1!==a.userAgent.indexOf("Series40")),u=new i;u.onerror=function(){n.method="png",n.href=r[2],t(r[2])},u.onload=function(){var e=1===u.width&&1===u.height,a=r[e&&c?0:e?1:2];n.method=e&&c?"svg":e?"datapng":"png",n.href=a,t(a,null,null,o)},u.src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==",document.documentElement.className+=" grunticon"}};n.loadCSS=t,e.grunticon=n})(this);(function(e,t){"use strict";var n=t.document,r="grunticon:",o=function(e){if(n.attachEvent?"complete"===n.readyState:"loading"!==n.readyState)e();else{var t=!1;n.addEventListener("readystatechange",function(){t||(t=!0,e())},!1)}},a=function(e){return t.document.querySelector('link[href$="'+e+'"]')},c=function(e){var t,n,o,a,c,i,u={};if(t=e.sheet,!t)return u;n=t.cssRules?t.cssRules:t.rules;for(var l=0;n.length>l;l++)o=n[l].cssText,a=r+n[l].selectorText,c=o.split(");")[0].match(/US\-ASCII\,([^"']+)/),c&&c[1]&&(i=decodeURIComponent(c[1]),u[a]=i);return u},i=function(e){var t,o,a;o="data-grunticon-embed";for(var c in e)if(a=c.slice(r.length),t=n.querySelectorAll(a+"["+o+"]"),t.length)for(var i=0;t.length>i;i++)t[i].innerHTML=e[c],t[i].style.backgroundImage="none",t[i].removeAttribute(o);return t},u=function(){"svg"===e.method&&o(function(){i(c(a(e.href))),e.embedComplete&&e.embedComplete()})};e.embedIcons=i,e.getCSS=a,e.getIcons=c,e.ready=o,e.svgLoadedCallback=u})(grunticon,this);

;(function( doc ) {
	// IE9+
	if( !( 'geolocation' in navigator ) || !( 'addEventListener' in window ) ) {
		return;
	}

	var templateName = doc.querySelector( 'meta[name="template"]' );
	doc.documentElement.className += ' enhanced-js' +
		( templateName ? " tmpl-" + templateName.content : "" ) +
		// gradient inference
		( 'matchMedia' in window ? " has-gradient" : "" );

	// Load the deferred JS onload
	window.addEventListener( "load", function() {
		var ref = doc.getElementsByTagName( "script" )[ 0 ];
		var defer = doc.createElement( "script" );
		defer.async = true;
		defer.src = NEJSConf.getDistFolder() + "defer.min.js";
		ref.parentNode.insertBefore( defer, ref );
	}, false)
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
		distFolder + "icons/icons.fallback.css" ], function(){
			grunticon.svgLoadedCallback();
			if( grunticon.method ){
				doc.documentElement.className += " grunticon-" + grunticon.method;
			}
		});

})( document );

// TypeKit
;(function( d ) {
	// Starts with Heavy "effra-n9" 'myi3gat'
	// Defers with Heavy Italic "effra-i9", Regular "effra-n4" 'wvu3pqt'

	// All Combined
	var config = {
		kitId: 'ppn2vqj',
		scriptTimeout: 3000
	},
	h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='//use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
})( document );