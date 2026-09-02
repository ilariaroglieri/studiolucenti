import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Swup from 'swup';
import SwupHeadPlugin from '@swup/head-plugin';
import SwupBodyClassPlugin from '@swup/body-class-plugin';
import { groupByRows } from './helpers';
import { DUR, EASE, STAGGER, prefersReducedMotion } from './tokens';

gsap.registerPlugin(ScrollTrigger, SplitText);

let lenis = null;

// Con movimento ridotto Lenis non viene istanziato: lo scroll smooth è
// movimento anche quando nessun elemento si anima. Ogni scrollTo passa di qui.
function scrollTo(target, options = {}) {
  if (lenis) {
    lenis.scrollTo(target, options);
    return;
  }
  const top =
    typeof target === 'number'
      ? target
      : (target?.getBoundingClientRect().top ?? 0) + window.scrollY;
  window.scrollTo({ top, behavior: 'auto' });
}

// Con movimento ridotto lo stato finale è già quello del CSS: un gsap.from()
// non va accorciato, va evitato. Anche 10ms di stato iniziale sono un salto.
function reveal(targets, vars) {
  const items = gsap.utils.toArray(targets);
  if (!items.length) return null;
  if (prefersReducedMotion()) return null;
  return gsap.from(items, vars);
}

// =============================
// Hero — allargamento all'attivazione
// =============================
// L'hero parte come loop muto in autoplay, quindi `play` scatta da solo al
// caricamento: non può essere quello a innescare l'allargamento. Il segnale
// è `lucenti:hero-activate`, che custom.js emette quando l'utente clicca il
// cerchio al centro. Solo da quel momento in poi play e pause governano
// l'allargamento, come su un film qualsiasi.
//
// L'ascolto è sul <video>, che esiste da subito: Plyr lo avvolge ma non lo
// sostituisce, quindi il listener sopravvive alla sua inizializzazione —
// initPlugins() e initAnimations() girano in parallelo, senza garanzia di
// ordine fra loro.
//
// `hero_background_video` resta per i loop puramente ambientali, senza Plyr
// e senza cerchio: restano un loop muto per sempre.

function initHeroExpand(reduced) {
  const heroSection = document.querySelector('#hero-section');
  const heroVideo = heroSection?.querySelector('.hero-video');
  const heroInner = heroSection?.querySelector(':scope > .container');
  if (!heroVideo || !heroInner) return;

  // L'allargamento è la più ampia delle animazioni del sito: con movimento
  // ridotto si toglie, non si tara più piano. Il video resta comunque
  // guardabile con i controlli Plyr, semplicemente non si allarga più.
  if (reduced) return;

  const siteHeader = document.querySelector('header');
  const videoContainer = heroSection.querySelector('.video-container');

  // Misurato all'attivazione e non qui: al primo load `.content` è ancora in
  // dissolvenza e il layout può non essere assestato.
  let originalW;
  let originalML;
  let originalMR;

  const expand = () => {
    if (originalW === undefined) {
      const cs = getComputedStyle(heroInner);
      originalW = cs.width;
      originalML = cs.marginLeft;
      originalMR = cs.marginRight;
    }
    gsap.to(heroInner, {
      width: '100%',
      marginLeft: 0,
      marginRight: 0,
      duration: DUR.base,
      ease: EASE.inOut,
    });
    gsap.to(siteHeader, {
      yPercent: -100,
      autoAlpha: 0,
      duration: DUR.base,
      ease: EASE.inOut,
    });
    scrollTo(videoContainer ?? heroSection, { duration: DUR.page, offset: 0 });
  };

  const collapse = () => {
    if (originalW === undefined) return;
    gsap.to(heroInner, {
      width: originalW,
      marginLeft: originalML,
      marginRight: originalMR,
      duration: DUR.base,
      ease: EASE.inOut,
      onComplete: () => gsap.set(heroInner, { clearProps: 'width,marginLeft,marginRight' }),
    });
    gsap.to(siteHeader, {
      yPercent: 0,
      autoAlpha: 1,
      duration: DUR.base,
      ease: EASE.inOut,
      onComplete: () => gsap.set(siteHeader, { clearProps: 'yPercent,opacity,visibility' }),
    });
  };

  heroVideo.addEventListener(
    'lucenti:hero-activate',
    () => {
      expand();
      heroVideo.addEventListener('play', expand);
      heroVideo.addEventListener('pause', collapse);
      heroVideo.addEventListener('ended', collapse);
    },
    { once: true }
  );
}

// =============================
// Specular sweep sul wordmark
// =============================
// Il motif identitario del sito: una banda di luce che attraversa le lettere
// da sinistra a destra, **una volta**. Al primo load e a ogni hover, mai in
// ciclo — un riflesso che si ripete non è un riflesso, è uno shimmer.
//
// Il gradiente e il suo stato di riposo stanno in `style.scss`: qui si muove
// solo `--sweep`, da 100% a 0%. La direzione non è ovvia e vale scriverla: con
// lo sfondo largo il triplo dell'elemento, la finestra visibile scorre nella
// direzione **opposta** al numero, quindi 100 → 0 porta la luce da sinistra a
// destra, non il contrario.
//
// Easing: `--ease-inout`, non `--ease-out`. Un riflesso che decelera in fondo
// si legge come qualcosa che si ferma sulla parola; qui deve passare.
// La durata è `--dur-page` (1000ms): la nota dice "~1200ms", ma i token sono
// tre e non se ne inventano altri — mille millisecondi stanno dentro il "circa".
//
// Vive fuori da `initAnimations()` di proposito: l'header **non** è nel
// container di Swup, sopravvive alle navigazioni, e reinizializzarlo a ogni
// visita accumulerebbe un listener di hover per pagina visitata.
// Un tween per elemento, non uno globale: il motif vive a due scale — il
// wordmark e il `404` — e il guardiano "un passaggio per volta" deve valere
// per ciascuno, non fra i due.
const sweepTweens = new WeakMap();

function playSpecularSweep(el, delay = 0) {
  // Interrogata qui e non all'avvio: con Swup la pagina non si ricarica mai, e
  // l'impostazione di sistema può cambiare a sito aperto.
  if (!el || prefersReducedMotion()) return;

  sweepTweens.set(
    el,
    gsap.fromTo(
      el,
      { '--sweep': '100%' },
      {
        '--sweep': '0%',
        duration: DUR.page,
        ease: EASE.inOut,
        delay,
        // Rimessa a riposo alla fine, o il passaggio successivo partirebbe con
        // la luce già dall'altra parte e attraverserebbe all'indietro.
        onComplete: () => { gsap.set(el, { '--sweep': '100%' }); },
      }
    )
  );
}

/**
 * @param {Element|null} el
 * @param {number|null} loadDelay `null` = nessun passaggio al load, solo hover.
 */
function armSpecularSweep(el, loadDelay = 0) {
  if (!el) return;

  if (loadDelay !== null) playSpecularSweep(el, loadDelay);

  el.addEventListener('mouseenter', () => {
    // Un passaggio per volta. Senza questa riga, entrare e uscire in fretta
    // dall'elemento fa ripartire la tween da capo a ogni ingresso e la luce
    // sfarfalla invece di attraversare.
    if (sweepTweens.get(el)?.isActive()) return;
    playSpecularSweep(el);
  });
}

function initSpecularSweep() {
  // Al load parte **per ultimo**, non insieme al resto: prima il contenuto
  // entra in dissolvenza, poi il reveal per parole lo compone, e solo allora
  // passa la luce. Sono tre cose in fila, non tre cose addosso — che è il modo
  // in cui la regola "una animazione protagonista per schermata" si rispetta
  // anche quando una schermata ne contiene più di una.
  //
  // Sul 404 il wordmark **non** fa il passaggio al load: lì la luce è del
  // numero, che è l'elemento della schermata. L'hover resta disponibile.
  const soloHover = document.body.classList.contains('error404');

  armSpecularSweep(document.querySelector('#site-name a'), soloHover ? null : DUR.page);
}

// =============================
// Reel della home — parallasse
// =============================
// Il video è più alto della finestra che lo ritaglia (`--reel-overscan` in
// `style.scss`): qui si sposta dentro quel margine mentre la pagina scorre.
// La corsa è meno di metà dell'eccedenza per lato, quindi il bordo del
// riquadro non si scopre mai — nemmeno con un rimbalzo di Lenis a fine pagina.
//
// `yPercent` e non `y`: è una frazione dell'altezza del *video*, quindi il
// valore resta giusto a qualsiasi larghezza e non va ricalcolato al resize.
// La centratura a riposo la fa il CSS con `top`, apposta perché la transform
// resti tutta di GSAP.
//
// L'eccedenza è 16% dell'altezza del riquadro, cioè 8% per lato, che sul video
// (alto 116%) vale 6,9%: sotto quel numero si sta dentro con margine.
const REEL_SHIFT = 5; // % dell'altezza del video, per lato

function initReelParallax(reduced) {
  if (reduced) return;

  const container = document.querySelector('#video-reel .video-container');
  const video = container?.querySelector('video');
  if (!video) return;

  gsap.fromTo(
    video,
    { yPercent: REEL_SHIFT },
    {
      yPercent: -REEL_SHIFT,
      // Nessun easing dei token qui, ed è voluto: con `scrub` la curva la
      // disegna lo scroll: una seconda curva sopra farebbe accelerare
      // l'inquadratura a scroll costante, che è esattamente l'effetto
      // scollegato dal dito che si vuole evitare.
      ease: 'none',
      scrollTrigger: {
        trigger: container,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true,
      },
    }
  );
}

function initAnimations() {
  const body = document.body;
  const reduced = prefersReducedMotion();

  initReelParallax(reduced);

  // La seconda scala del motif. Il 404 è l'unica schermata del sito senza
  // nient'altro in movimento, quindi l'unica che ha uno slot libero da darle.
  //
  // Qui dentro e non in `initSpecularSweep()` perché `#error-code` **è** nel
  // container di Swup: va riarmato a ogni visita, al contrario del wordmark
  // che sopravvive alle navigazioni e va armato una volta sola.
  armSpecularSweep(document.querySelector('#error-code'), DUR.base);

  // home + archive: animate project rows
  if (body.classList.contains('home') || body.classList.contains('blog')) {
    const projects = gsap.utils.toArray('.project');
    if (projects.length) {
      const rows = groupByRows(projects);
      rows.forEach((row) => {
        // niente skewY in entrata: lo skew appartiene allo scroll, non al reveal
        reveal(row.items, {
          yPercent: 20,
          opacity: 0,
          duration: DUR.base,
          ease: EASE.out,
          stagger: STAGGER.base,
          scrollTrigger: {
            trigger: row.items[0],
            start: 'top 85%',
            once: true,
          },
        });
      });
    }
  }

  // single page
  if (body.classList.contains('single')) {
    initHeroExpand(reduced);

    // animate flex-row elements + text elements
    gsap.utils.toArray('.single .flex-row').forEach((row) => {
      const inViewport = row.getBoundingClientRect().top < window.innerHeight;
      const scrollOpts = inViewport
        ? {}
        : { scrollTrigger: { trigger: row, start: 'top bottom', once: true } };

      reveal(row.querySelectorAll('.element'), {
        yPercent: 20,
        opacity: 0,
        duration: DUR.base,
        ease: EASE.out,
        stagger: STAGGER.base,
        ...scrollOpts,
      });

      reveal(row.querySelectorAll('.text-element'), {
        yPercent: 20,
        opacity: 0,
        duration: DUR.base,
        ease: EASE.out,
        stagger: STAGGER.base,
        ...scrollOpts,
      });
    });
  }

  // Reveal tipografico
  //
  // Due usi dello stesso gesto: il **titolo** del progetto, che è l'elemento
  // singolo della sua schermata, e il **corpo del testo**, dove prima girava
  // uno split per righe. Per **parole** in entrambi i casi, mai per lettera:
  // un testo che si sbriciola è nella lista delle cose da non fare mai.
  //
  // Con movimento ridotto non si splitta nemmeno — SplitText riscrive il
  // markup in <div> annidati, e se la tween non parte il testo resterebbe
  // dentro una maschera a opacità 0.
  if (!reduced) {
    const title = document.querySelector('.single .project-title-words');

    if (title) {
      const split = SplitText.create(title, {
        type: 'words',
        // Una maschera per parola, non una sul contenitore: se il titolo va a
        // capo, un unico `overflow: hidden` taglierebbe la riga sopra mentre
        // quella sotto sale. Così ogni parola esce dalla propria.
        mask: 'words',
      });

      if (split.words.length) {
        // Stessa logica dei blocchi qui sopra: se il titolo nasce sotto la
        // piega — su un portatile basso l'hero da solo se la mangia — il
        // reveal aspetta di essere guardato invece di consumarsi da solo.
        const inViewport = title.getBoundingClientRect().top < window.innerHeight;
        const scrollOpts = inViewport
          ? {}
          : { scrollTrigger: { trigger: title, start: 'top bottom', once: true } };

        gsap.from(split.words, {
          // 0.6em, non il 100% dell'altezza: la parola affiora, non si
          // ribalta. È la differenza fra un reveal e un effetto da tutorial.
          y: '0.6em',
          autoAlpha: 0,
          duration: DUR.base,
          ease: EASE.out,
          stagger: STAGGER.words,
          ...scrollOpts,
          onComplete: () => { split.revert(); },
        });
      } else {
        split.revert();
      }
    }

    // Il corpo del testo — intro della home, About, descrizione del progetto,
    // moduli di testo. Stesso gesto del titolo, stessa ampiezza: cambia solo
    // come si distribuisce lo stagger.
    gsap.utils.toArray('.text-element-lines').forEach((el) => {
      const split = SplitText.create(el, { type: 'words', mask: 'words' });

      if (!split.words.length) {
        split.revert();
        return;
      }

      gsap.from(split.words, {
        y: '0.6em',
        autoAlpha: 0,
        duration: DUR.base,
        ease: EASE.out,
        // `amount` e non `each`, ed è la differenza fra un titolo e un
        // paragrafo: l'intro della home è una quarantina di parole, e a 40ms
        // l'una lo stagger da solo durerebbe un secondo e sei — il testo
        // starebbe ancora arrivando mentre lo si è già scrollato via. Con
        // `amount` il blocco si rivela sempre nella stessa finestra, che sia
        // di dieci parole o di cinquanta.
        stagger: { amount: DUR.base },
        scrollTrigger: { trigger: el, start: 'top 85%', once: true },
        onComplete: () => { split.revert(); },
      });
    });
  }

  // about page
  const infos = document.querySelector('#infos');
  if (infos) {
    reveal('#info-list .text-element, #clients-list .text-element', {
      yPercent: 20,
      opacity: 0,
      duration: DUR.base,
      ease: EASE.out,
      stagger: STAGGER.base,
      scrollTrigger: { trigger: infos, start: 'top 85%', once: true },
    });
  }

  ScrollTrigger.refresh();
}

// =============================
// Skew legato alla velocità di scroll
// =============================
// Le immagini si inclinano quanto più veloce va lo scroll, e tornano dritte da
// sole. Il limite è il punto: oltre i 2–3 gradi diventa il portfolio del 2022.
//
// Una sola scrittura per frame, su <html>: `--skew` è ereditata da tutte le
// `.media-container`, quindi il costo non cresce con il numero di thumbnail e
// non c'è niente da smontare quando Swup sostituisce il contenuto.

const MAX_SKEW = 2;          // gradi
const SKEW_SATURATION = 80;  // px/frame ai quali si tocca MAX_SKEW

// Sopra questa soglia non è uno scroll: è un salto. Il browser che ripristina
// la posizione dopo un reload, un'ancora, lo scrollTo(0) di una navigazione.
// Lenis li fa passare tutti da `onNativeScroll`, che emette **un solo evento**
// con `velocity` pari all'intero salto — migliaia di px contro le decine di una
// rotella. Senza questo filtro ogni media scattava al massimo dello skew
// insieme agli altri, che è esattamente quello che si vedeva al reload.
const JUMP_VELOCITY = 400;   // px/frame

// Costante di tempo dell'inseguimento. Non è un lerp per frame a caso: con
// tau = --dur-micro / 3 lo skew copre il 95% della distanza in --dur-micro,
// che è il token del feedback immediato — ed è quello che deve essere.
// Scritta come esponenziale sul delta reale, così non cambia comportamento
// fra 60 e 120 Hz.
const SKEW_TAU = (DUR.micro * 1000) / 3;

let targetSkew = 0;
let currentSkew = 0;
let skewArmed = false;

function writeSkew(value) {
  currentSkew = value;
  document.documentElement.style.setProperty('--skew', value.toFixed(3));
}

function initScrollSkew() {
  // Con movimento ridotto Lenis non viene istanziato, e con lui non esiste
  // nemmeno questo: `--skew` resta 0 e la regola CSS è inerte.
  if (!lenis) return;

  skewArmed = true;
  const clampSkew = gsap.utils.clamp(-MAX_SKEW, MAX_SKEW);

  lenis.on('scroll', ({ velocity }) => {
    if (Math.abs(velocity) > JUMP_VELOCITY) return;
    targetSkew = clampSkew((velocity / SKEW_SATURATION) * MAX_SKEW);
  });

  gsap.ticker.add((time, deltaTime) => {
    const alpha = 1 - Math.exp(-deltaTime / SKEW_TAU);
    const next = currentSkew + (targetSkew - currentSkew) * alpha;
    // sotto il centesimo di grado si azzera, così la variabile smette di essere
    // riscritta invece di oscillare all'infinito su cifre che nessuno vede
    const value = Math.abs(next) < 0.01 && Math.abs(targetSkew) < 0.01 ? 0 : next;
    if (value === currentSkew) return;
    writeSkew(value);
  });
}

// Le navigazioni Swup riportano lo scroll in cima: lì lo skew va azzerato
// subito, non accompagnato.
function resetScrollSkew() {
  if (!skewArmed) return;
  targetSkew = 0;
  writeSkew(0);
}

// =============================
// FLIP griglia → progetto
// =============================
// Swup gira con `native: true`: la sostituzione del contenuto è avvolta in
// document.startViewTransition(). Il morph nasce da due `view-transition-name`
// identici sui due lati della navigazione — la thumbnail cliccata e l'hero
// della scheda. La taratura in tempo sta in `_view-transitions.scss`.

// ── Interruttore ──────────────────────────────────────────
// DISATTIVATA il 28 agosto 2026. Il morph regge solo se la thumbnail e l'hero
// sono lo stesso materiale, e oggi non lo sono: parecchi progetti non hanno
// un hero (e la thumbnail resta senza approdo, quindi si dissolve da sola),
// in altri l'hero è un'immagine diversa dalla thumbnail e il morph legge come
// uno scambio, non come una continuità.
//
// Con `false` Swup non usa affatto le view transition e ricade sul crossfade
// GSAP: tutto quello che sta sotto si spegne da solo, perché ogni gancio è
// condizionato a `isNative(visit)`, che segue questo flag.
//
// Per riaccenderla basta rimettere `true`, ma prima va risolto il contenuto —
// vedi [[Fase 2 — Task list]] → H.
const FLIP_ENABLED = false;

const VT_NAME = 'project-hero';

function clearTransitionName() {
  document.querySelectorAll('[data-vt-active]').forEach((el) => {
    el.style.removeProperty('view-transition-name');
    el.removeAttribute('data-vt-active');
  });
}

// Il nome deve essere unico nella pagina: se compare due volte il browser
// annulla la transizione in silenzio, senza errori in console. Si pulisce
// quindi sempre prima di assegnare, mai in due punti diversi.
function setTransitionName(el) {
  clearTransitionName();
  if (!el) return;
  el.style.setProperty('view-transition-name', VT_NAME);
  el.setAttribute('data-vt-active', '');
}

function mediaOfProject(el) {
  return el?.closest('project')?.querySelector('[data-vt-media]') ?? null;
}

function samePath(href, url) {
  try {
    return new URL(href, window.location.origin).pathname
        === new URL(url, window.location.origin).pathname;
  } catch (_) {
    return false;
  }
}

function projectMediaByUrl(url) {
  if (!url) return null;
  const link = Array.from(document.querySelectorAll('project a.overall'))
    .find((a) => samePath(a.href, url));
  return mediaOfProject(link);
}

// Dove atterra il morph nella pagina appena inserita: sulla scheda è l'hero,
// tornando in griglia è la thumbnail del progetto da cui si arriva — così il
// tasto indietro riavvolge lo stesso gesto invece di sfumare.
function transitionTarget(visit) {
  return (
    document.querySelector('#hero-section [data-vt-media]') ??
    projectMediaByUrl(visit.from.url)
  );
}

// Swup spegne `native` da solo se il browser non ha startViewTransition
// (oggi: Firefox), e lì resta il crossfade GSAP.
const isNative = (visit) => !!visit.animation.native;

// Il primo paint non deve aspettare i font: con il preload document.fonts.ready
// arriva presto, ma oltre questa soglia si rivela comunque.
const REVEAL_FONT_TIMEOUT = 400;

function fontsSettled() {
  return Promise.race([
    document.fonts.ready,
    new Promise((resolve) => { setTimeout(resolve, REVEAL_FONT_TIMEOUT); }),
  ]);
}

function revealContent(delay = DUR.base) {
  // con movimento ridotto il gate va tolto, non attraversato in dissolvenza:
  // qui serve davvero un set() allo stato finale, l'opacità 0 arriva dal CSS
  if (prefersReducedMotion()) {
    document.documentElement.classList.remove('is-loading');
    gsap.set(['.content', 'footer'], { opacity: 1 });
    return;
  }

  // l'opacità va ripresa inline PRIMA di togliere la classe: altrimenti gsap
  // legge già 1 come valore di partenza e il fade in ingresso sparisce
  gsap.set(['.content', 'footer'], { opacity: 0 });
  document.documentElement.classList.remove('is-loading');
  gsap.to('.content', { opacity: 1, duration: DUR.base, ease: EASE.out });
  gsap.to('footer', { opacity: 1, duration: DUR.base, delay });
}

// resize: un solo refresh a raffica finita. Su mobile la barra degli indirizzi
// che si ritrae emette resize durante lo scroll, e ogni refresh ricalcola tutto.
function onIdleResize(callback, wait = 200) {
  let timer;
  let lastW = window.innerWidth;
  let lastH = window.innerHeight;

  return () => {
    const w = window.innerWidth;
    const h = window.innerHeight;
    const isChromeCollapse = w === lastW && Math.abs(h - lastH) < 120;
    lastW = w;
    lastH = h;
    if (isChromeCollapse) return;

    clearTimeout(timer);
    timer = setTimeout(callback, wait);
  };
}

document.addEventListener('DOMContentLoaded', () => {
  // un solo driver: il ticker GSAP. Con autoRaf: true Lenis avanzava due volte
  // per frame e lo smooth scroll diventava irregolare.
  if (!prefersReducedMotion()) {
    lenis = new Lenis();
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => { lenis.raf(time * 1000); });
    gsap.ticker.lagSmoothing(0);
    initScrollSkew();
  }

  const swup = new Swup({
    containers: ['.content'],
    animationSelector: false,
    animateHistoryBrowsing: true,
    // il browser avvolge la sostituzione in una view transition; Swup
    // disattiva l'opzione da solo dove l'API non c'è (oggi: Firefox)
    native: FLIP_ENABLED,
    ignoreVisit: (url, { el } = {}) => !!el?.closest('a.single-lightbox-el'),
    plugins: [
      new SwupHeadPlugin(),
      new SwupBodyClassPlugin(),
    ],
  });
  window.swup = swup;

  // Initial page load
  fontsSettled().then(() => {
    initAnimations();
    revealContent();
    // Una volta sola: l'header non è nel container di Swup e non viene
    // rimontato, quindi non passa da `initAnimations()`.
    initSpecularSweep();
  });

  // Solo la thumbnail cliccata prende il nome. `a.overall` è il link che copre
  // la scheda in griglia: è l'unico click che deve produrre un morph.
  swup.hooks.on('visit:start', (visit) => {
    if (!isNative(visit)) return;
    // sul popstate non c'è nessun click, e il nome è già sull'hero da quando
    // la scheda è entrata: è quello che fa tornare indietro il morph
    if (visit.history.popstate) return;
    setTransitionName(mediaOfProject(visit.trigger.el?.closest('a.overall')));
  });

  // Leave: hide footer instantly (avoids bleed-through as content fades), then fade content
  swup.hooks.on('animation:out:start', (visit) => {
    // Con la view transition nativa l'uscita la disegna il browser. Un fade
    // GSAP qui fotograferebbe la pagina vecchia già a opacità 0 e la
    // transizione sfumerebbe nel vuoto.
    if (isNative(visit)) return undefined;

    gsap.set('footer', { opacity: 0 });
    if (prefersReducedMotion()) return gsap.set('.content', { opacity: 0 });
    // --dur-page è il budget dell'intera transizione: metà in uscita, metà in entrata
    return gsap.to('.content', { opacity: 0, duration: DUR.page / 2, ease: EASE.inOut });
  });

  // After DOM swap: kill old ScrollTriggers, scroll to top
  swup.hooks.on('content:replace', (visit) => {
    ScrollTrigger.getAll().forEach((t) => t.kill());
    scrollTo(0, { immediate: true });
    resetScrollSkew();

    if (isNative(visit)) {
      // Siamo dentro il callback di startViewTransition: lo snapshot della
      // pagina nuova viene preso quando questo blocco è finito. Le animazioni
      // vanno inizializzate adesso, o gli elementi verrebbero fotografati
      // visibili e sparirebbero un attimo dopo, all'inizio del loro reveal.
      setTransitionName(transitionTarget(visit));
      initAnimations();
      return;
    }

    // Il container nuovo nasce senza opacità inline (il gate CSS vale solo al
    // primo load): va nascosto qui, o lampeggia prima del fade in ingresso.
    gsap.set('.content', { opacity: 0 });
  });

  // Enter: re-init animations, fade in content + footer
  swup.hooks.on('animation:in:start', async (visit) => {
    if (isNative(visit)) return undefined; // già fatto dentro la view transition

    await fontsSettled();
    initAnimations();

    if (prefersReducedMotion()) return gsap.set(['.content', 'footer'], { opacity: 1 });

    gsap.to('footer', { opacity: 1, duration: DUR.base, delay: DUR.base });
    return gsap.to('.content', { opacity: 1, duration: DUR.page / 2, ease: EASE.inOut });
  });

  window.addEventListener('load', () => {
    ScrollTrigger.refresh();
  });

  window.addEventListener('resize', onIdleResize(() => ScrollTrigger.refresh()));
});
