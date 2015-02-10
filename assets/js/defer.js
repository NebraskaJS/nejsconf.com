// Static Hover
;(function( doc ) {
	// IE9+
	if( !( 'geolocation' in navigator ) ) {
		return;
	}

	// Thank you to http://jsfiddle.net/AbdiasSoftware/FrMNL/
	var logoHover = doc.querySelector( '.logo-hover' );
	function addStatic() {
		logoHover.removeEventListener( "mouseover", addStatic );
		var canvas = doc.querySelector( '.logo-static canvas' ),
			ctx = canvas.getContext('2d');

		canvas.width = 230;
		canvas.height = 130;

		function noise(ctx) {
			var w = ctx.canvas.width,
				h = ctx.canvas.height,
				idata = ctx.createImageData(w, h),
				buffer32 = new Uint32Array(idata.data.buffer),
				len = buffer32.length,
				i = 0;

			for(; i < len;i++) {
				if (Math.random() < 0.5) {
					buffer32[i] = 0xff000000;
				}
			}
			
			ctx.putImageData(idata, 0, 0);
		}

		var toggle = true;
		// added toggle to get 30 FPS instead of 60 FPS
		(function loop() {
			toggle = !toggle;
			if (toggle) {
				requestAnimationFrame(loop);
				return;
			}
			noise(ctx);
			requestAnimationFrame(loop);
		})();
	}

	logoHover.addEventListener( "mouseover", addStatic, false );
})( document );

// Google Analytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-33622676-2', 'auto');
ga('send', 'pageview');