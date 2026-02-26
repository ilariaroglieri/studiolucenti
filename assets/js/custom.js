function checkScroll() {
	var currentScrollPos = $(window).scrollTop();

  if ($('body').hasClass('home')) {
  	if (currentScrollPos > window.innerHeight) {
  		$('.icon, #logo, .menu-toggle').removeClass('white');
    } else {
  		$('.icon, #logo, .menu-toggle').addClass('white');
    }
  }
}

//-----------DOCUMENT.READY----------------

jQuery(document).ready(function($) {

// --- header behaviour
	
  // scroll events
  var prevScrollPos = $(window).scrollTop();
  $(window).scroll(function() {
    checkScroll();

    var currentScrollPos = $(window).scrollTop();
    if (prevScrollPos > currentScrollPos && prevScrollPos > 0) {
	    $('header').addClass('visible')
	  } else {
	    $('header').removeClass('visible')
	  }

	  prevScrollPos = currentScrollPos;
  });

  checkScroll();


// --- Hamburger menu
  $('.menu-toggle').click(function() {
    $(this).toggleClass('open');
    $('header').toggleClass('active');
    $('body').toggleClass('blocked');
  });

// --- Lightbox
  var lightbox = $('a.single-lightbox-el').simpleLightbox({
    showCounter: true, 
    overlayOpacity: .9,
    closeText: 'Close',
    animationSpeed: 500,
    animationSlide: false,
    navText: [
      '<svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M25.1965 1.08765L2.19653 22.9289L25.1965 44.0876" stroke="#323232" stroke-width="2"/></svg>',
      '<svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.03272 1.08765L24.0327 22.9289L1.03272 44.0876" stroke="#323232" stroke-width="2"/></svg>'
  ]
  });



//----------END JQUERY -----------
});
