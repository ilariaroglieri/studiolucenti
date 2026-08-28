import 'plyr/dist/plyr.css';
import 'simplelightbox/dist/simple-lightbox.min.css';
import 'swiper/css/bundle';
import { prefersReducedMotion } from './tokens';

function updateHeaderHeight() {
  const header = document.querySelector('header');
  if (header) document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
}
updateHeaderHeight();

// offsetHeight forza il layout: a ogni resize, con la barra degli indirizzi
// mobile che si ritrae durante lo scroll, era un layout forzato per evento.
let headerHeightTimer;
let lastViewportWidth = window.innerWidth;
window.addEventListener('resize', () => {
  const width = window.innerWidth;
  const widthChanged = width !== lastViewportWidth;
  lastViewportWidth = width;
  if (!widthChanged) return;

  clearTimeout(headerHeightTimer);
  headerHeightTimer = setTimeout(updateHeaderHeight, 200);
});

let players = [];
let hlsInstances = [];
let swipers = [];
let lightbox = null;
let lazyVideoObserver = null;

function cleanupPlugins() {
  cleanupLazyVideos();
  players.forEach((p) => { try { p.destroy(); } catch (_) {} });
  players = [];
  hlsInstances.forEach((h) => { try { h.destroy(); } catch (_) {} });
  hlsInstances = [];
  swipers.forEach((s) => { try { s.destroy(); } catch (_) {} });
  swipers = [];
  if (lightbox) { try { lightbox.destroy(); } catch (_) {} lightbox = null; }
}

// =============================
// Loop di griglia: caricamento a viewport
// =============================
// I <video> fuori dall'hero escono dal PHP con `preload="none"` e le sorgenti
// su `data-src`: senza questo codice mostrano il poster e basta. È il modo in
// cui una homepage piena di loop resta sotto i 2 MB al primo caricamento.

function loadLazyVideo(video) {
  if (video.dataset.lazyLoaded) return;
  video.dataset.lazyLoaded = '1';

  const sources = video.querySelectorAll('source[data-src]');
  if (!sources.length) return;

  sources.forEach((source) => {
    source.src = source.dataset.src;
    source.removeAttribute('data-src');
  });

  // senza load() il browser ignora le <source> aggiunte dopo il parsing
  video.load();
}

function initLazyVideos() {
  // Con movimento ridotto non si assegna nessuna sorgente: resta il poster,
  // e il video non viene nemmeno scaricato.
  if (prefersReducedMotion()) return;

  const videos = document.querySelectorAll('video.js-lazy-video');
  if (!videos.length) return;

  if (!('IntersectionObserver' in window)) {
    videos.forEach(loadLazyVideo);
    return;
  }

  lazyVideoObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(({ target, isIntersecting }) => {
        if (isIntersecting) {
          loadLazyVideo(target);
          // può essere rifiutata (scheda in background, risparmio energetico):
          // non è un errore e non va lasciata come promise non gestita
          target.play?.().catch(() => {});
        } else if (target.dataset.lazyLoaded) {
          // pausa all'uscita, senza toccare la sorgente: rientrando riparte
          // dal frame dov'era, senza un secondo download
          target.pause();
        }
      });
    },
    // il video comincia a caricare poco prima di essere visibile, così il
    // primo frame è pronto quando arriva davvero sotto gli occhi
    { rootMargin: '200px 0px' }
  );

  videos.forEach((video) => lazyVideoObserver.observe(video));
}

// Obbligatorio: Swup rimonta i contenuti, e un observer non disconnesso resta
// appeso ai nodi della pagina vecchia insieme a tutto quello che tocca.
function cleanupLazyVideos() {
  if (!lazyVideoObserver) return;
  lazyVideoObserver.disconnect();
  lazyVideoObserver = null;
}

// Con movimento ridotto i video in autoplay restano fermi sul poster.
// Va rifatto dopo ogni sostituzione di contenuto: l'attributo `autoplay` è nel
// markup, e Plyr o hls.js possono comunque far partire il media dopo l'attach.
function freezeAutoplayVideos() {
  if (!prefersReducedMotion()) return;

  document.querySelectorAll('video[autoplay]').forEach((video) => {
    video.removeAttribute('autoplay');
    video.autoplay = false;
    video.pause();

    // I loop decorativi non hanno controlli: se ripartono da soli l'utente non
    // ha modo di fermarli. Quelli con controlli restano riproducibili a mano.
    if (!video.controls) {
      video.addEventListener('play', () => video.pause());
    }
  });
}

function initPlugins() {
  freezeAutoplayVideos();
  initLazyVideos();

  const hlsVideos = document.querySelectorAll('.hls-video');
  if (hlsVideos.length) {
    Promise.all([import('plyr'), import('hls.js')]).then(
      ([{ default: Plyr }, { default: Hls }]) => {
        hlsVideos.forEach((videoEl) => {
          const player = new Plyr(videoEl, {
            autoplay: !prefersReducedMotion(),
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'captions', 'airplay', 'fullscreen'],
          });
          const src = videoEl.querySelector('source')?.getAttribute('src');

          if (src && Hls.isSupported()) {
            const hls = new Hls();
            hlsInstances.push(hls);
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

  const bgVideos = document.querySelectorAll('.bg-video');
  if (bgVideos.length) {
    import('hls.js').then(({ default: Hls }) => {
      bgVideos.forEach((videoEl) => {
        const src = videoEl.querySelector('source')?.getAttribute('src');
        if (src && Hls.isSupported()) {
          const hls = new Hls();
          hlsInstances.push(hls);
          hls.loadSource(src);
          hls.attachMedia(videoEl);
        }
      });
    });
  }

  const sliders = document.querySelectorAll('.slider-module');
  if (sliders.length) {
    import('swiper/bundle').then(({ default: Swiper }) => {
      sliders.forEach((el) => {
        const container = el.closest('.slider-container');
        if (!container) return;
        const swiper = new Swiper(el, {
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
        swipers.push(swiper);
      });
    });
  }

  // Lightbox: check element existence directly, not body class
  // (body class may not yet be updated by SwupBodyClassPlugin at content:replace time)
  const lightboxEls = document.querySelectorAll('a.single-lightbox-el');
  if (lightboxEls.length) {
    import('simplelightbox').then(({ default: SimpleLightbox }) => {
      lightbox = new SimpleLightbox('a.single-lightbox-el', {
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

document.addEventListener('DOMContentLoaded', () => {
  initPlugins();

  // Swup hooks: cleanup before leave, re-init after content swap
  if (window.swup) {
    window.swup.hooks.on('visit:start', () => {
      document.querySelector('.menu-toggle')?.classList.remove('open');
      document.querySelector('header')?.classList.remove('active');
      document.body.classList.remove('blocked');
    });
    window.swup.hooks.on('animation:out:start', cleanupPlugins);
    window.swup.hooks.on('content:replace', initPlugins);
    // Swiper with loop:true needs a recalc after the content is visible
    window.swup.hooks.on('visit:end', () => {
      swipers.forEach((s) => { try { s.update(); } catch (_) {} });
    });
  }

  // jQuery-dependent UI (WordPress provides jQuery globally)
  jQuery(document).ready(function ($) {
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

    // Hide/show header on scroll.
    // Con Lenis gli eventi scroll arrivano a frequenza di frame: throttle su rAF
    // e scrittura sul DOM solo quando lo stato cambia davvero.
    const header = document.querySelector('header');
    let prevScrollPos = window.scrollY;
    let isVisible = null;
    let scrollTicking = false;

    function onScrollFrame() {
      scrollTicking = false;
      const currentScrollPos = window.scrollY;
      const shouldBeVisible = prevScrollPos > currentScrollPos && prevScrollPos > 0;
      prevScrollPos = currentScrollPos;

      if (shouldBeVisible === isVisible) return;
      isVisible = shouldBeVisible;
      header?.classList.toggle('visible', shouldBeVisible);
    }

    window.addEventListener('scroll', () => {
      if (scrollTicking) return;
      scrollTicking = true;
      requestAnimationFrame(onScrollFrame);
    }, { passive: true });
  });
});
