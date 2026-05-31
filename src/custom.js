import 'plyr/dist/plyr.css';
import 'simplelightbox/dist/simple-lightbox.min.css';

document.addEventListener('DOMContentLoaded', () => {
  // Plyr + HLS.js: loaded only on pages with the video element (~1.7 MB saved on other pages)
  if (document.getElementById('hls-video')) {
    Promise.all([import('plyr'), import('hls.js')]).then(
      ([{ default: Plyr }, { default: Hls }]) => {
        const player = new Plyr('#hls-video');
        const source = document.querySelector('#hls-video source');
        const src = source ? source.getAttribute('src') : null;

        if (src && Hls.isSupported()) {
          const hls = new Hls();
          hls.loadSource(src);
          hls.attachMedia(player.media);
        }
      }
    );
  }

  // SimpleLightbox: loaded only on single pages
  if (document.body.classList.contains('single')) {
    const lightboxEls = document.querySelectorAll('a.single-lightbox-el');
    if (lightboxEls.length) {
      import('simplelightbox').then(({ default: SimpleLightbox }) => {
        new SimpleLightbox('a.single-lightbox-el', {
          showCounter: true,
          overlayOpacity: 0.9,
          closeText: 'Close',
          animationSpeed: 500,
          animationSlide: false,
          navText: [
            '<svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M25.1965 1.08765L2.19653 22.9289L25.1965 44.0876" stroke="#323232" stroke-width="2"/></svg>',
            '<svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.03272 1.08765L24.0327 22.9289L1.03272 44.0876" stroke="#323232" stroke-width="2"/></svg>',
          ],
        });
      });
    }
  }
});

// jQuery-dependent UI (WordPress provides jQuery globally)
jQuery(document).ready(function ($) {
  // Fade in content
  $('.content').addClass('loaded');

  // Hamburger menu
  $('.menu-toggle').click(function () {
    $(this).toggleClass('open');
    $('header').toggleClass('active');
    $('body').toggleClass('blocked');
  });

  // Footer contact button scrolls to bottom
  $('.contact a').on('click', function (e) {
    e.preventDefault();
    $('.menu-toggle').removeClass('open');
    $('header').removeClass('active');
    window.scrollTo(0, document.body.scrollHeight);
  });

  // Hide/show header on scroll
  let prevScrollPos = $(window).scrollTop();
  $(window).scroll(function () {
    const currentScrollPos = $(window).scrollTop();
    if (prevScrollPos > currentScrollPos && prevScrollPos > 0) {
      $('header').addClass('visible');
    } else {
      $('header').removeClass('visible');
    }
    prevScrollPos = currentScrollPos;
  });

  // Show footer when near bottom
  $(window).scroll(function () {
    if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
      $('footer').addClass('visible');
    } else {
      $('footer').removeClass('visible');
    }
  });
});
