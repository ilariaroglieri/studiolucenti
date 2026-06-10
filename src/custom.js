import 'plyr/dist/plyr.css';
import 'simplelightbox/dist/simple-lightbox.min.css';
import 'swiper/css/bundle';

function updateHeaderHeight() {
  const header = document.querySelector('header');
  if (header) document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
}
updateHeaderHeight();
window.addEventListener('resize', updateHeaderHeight);

document.addEventListener('DOMContentLoaded', () => {
  // Plyr + HLS.js: loaded only on pages with video elements (~1.7 MB saved on other pages)
  const hlsVideos = document.querySelectorAll('.hls-video');
  if (hlsVideos.length) {
    Promise.all([import('plyr'), import('hls.js')]).then(
      ([{ default: Plyr }, { default: Hls }]) => {
        const players = [];

        hlsVideos.forEach((videoEl) => {
          const player = new Plyr(videoEl, {
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', /*'volume',*/ 'captions', 'airplay', 'fullscreen'],
          });
          const src = videoEl.querySelector('source')?.getAttribute('src');

          if (src && Hls.isSupported()) {
            const hls = new Hls();
            hls.loadSource(src);
            hls.attachMedia(player.media);
          }

          players.push(player);
        });

        players.forEach((player) => {
          player.on('play', () => {
            players.forEach((other) => {
              if (other !== player) other.pause();
            });
          });
        });
      }
    );
  }

  // Background videos: HLS only, no Plyr
  const bgVideos = document.querySelectorAll('.bg-video');
  if (bgVideos.length) {
    import('hls.js').then(({ default: Hls }) => {
      bgVideos.forEach((videoEl) => {
        const src = videoEl.querySelector('source')?.getAttribute('src');
        if (src && Hls.isSupported()) {
          const hls = new Hls();
          hls.loadSource(src);
          hls.attachMedia(videoEl);
        }
      });
    });
  }

  // Swiper: loaded only on pages with sliders
  const sliders = document.querySelectorAll('.slider-module');
  if (sliders.length) {
    import('swiper/bundle').then(({ default: Swiper }) => {
      sliders.forEach((el) => {
        const container = el.closest('.slider-container');
        new Swiper(el, {
          loop: true,
          navigation: {
            prevEl: container.querySelector('.slider-nav-prev'),
            nextEl: container.querySelector('.slider-nav-next'),
          },
          pagination: {
            el: container.querySelector('.slider-fraction'),
            type: 'fraction',
            renderFraction: (currentClass, totalClass) =>
              `<span class="${currentClass}"></span>/<span class="${totalClass}"></span>`,
          },
        });
      });
    });
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
  // Fade in content, then footer after content is fully opaque
  $('.content').addClass('loaded');
  setTimeout(() => $('footer').addClass('loaded'), 1000);

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

});
