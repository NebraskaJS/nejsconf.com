//@codekit-prepend "lib/modernizr-3.3.1.min.js","lib/jquery-1.12.0.js","lib/lazysizes.min.js","lib/jquery.waypoints.js","lib/sticky.js";
		
$(document).ready(function() {

	//SLIDE UP AND DOWN
  if (matchMedia('(min-width: 38em)').matches) {
    var href, pos, hash, buffer = 93;
  }
  else {
    var href, pos, hash, buffer = 0;
  }

  function Slider(id){
    $('html,body').animate({scrollTop: $(id).offset().top-buffer+'px'}, 'slow');
  }
  $('.nav a, a.scroll').click(function(e) {

    href = $(this).attr('href');
    pos = href.indexOf('#');
    hash = href.substring(pos);
    if ( pos !== -1 && hash.length > 1 && $(hash).length > 0 ) {
      e.preventDefault();
      Slider(hash);
    }
  });
  if( '' != location.hash ) { $('a[href=' + location.hash + ']').first().click(); }

});

var sticky = new Waypoint.Sticky({
  element: $('.nav')[0]
})
