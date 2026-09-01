import { GRAIN } from './tokens';
import { prefersReducedMotion } from './tokens';

// =============================
// Grana animata
// =============================
// Otto tessere di rumore da 128px, generate una volta sola e poi scambiate a
// 12 fps → [[Decisioni]] → D3. Il punto della decisione è tutto qui: le
// tessere non si ricalcolano mai, il browser si limita a scambiare un
// `background-image` già decodificato. `feTurbulence` animato variando
// `baseFrequency` rifarebbe invece il filtro a ogni frame.
//
// Il layer vive in `<body>`, fuori dal container di Swup: sopravvive alle
// navigazioni e non va reinizializzato.

const STEP = 1000 / GRAIN.fps;

let layer = null;
let tiles = [];
let frame = 0;
let last = 0;
let raf = null;

// Una tessera: rumore in scala di grigi, un valore casuale per pixel, alfa
// piena. L'alfa la fa il layer con la sua opacità, non i singoli pixel.
function makeTile() {
  const canvas = document.createElement('canvas');
  canvas.width = GRAIN.tile;
  canvas.height = GRAIN.tile;

  const ctx = canvas.getContext('2d');
  const image = ctx.createImageData(GRAIN.tile, GRAIN.tile);
  const data = image.data;

  for (let i = 0; i < data.length; i += 4) {
    const value = (Math.random() * 255) | 0;
    data[i] = value;
    data[i + 1] = value;
    data[i + 2] = value;
    data[i + 3] = 255;
  }
  ctx.putImageData(image, 0, 0);

  // Blob invece di `toDataURL`. Misurato: una tessera pesa 26 KB, otto ne fanno
  // 209 — il rumore non si comprime, è il caso peggiore per il PNG. In base64
  // diventerebbero ~280 KB di **stringhe** tenute vive per sempre nell'heap JS.
  // Il blob resta fuori dall'heap e il browser lo decodifica una volta sola.
  // Gli object URL non si revocano: devono durare quanto la pagina.
  return new Promise((resolve) => {
    canvas.toBlob((blob) => {
      resolve(blob ? URL.createObjectURL(blob) : canvas.toDataURL('image/png'));
    }, 'image/png');
  });
}

function draw() {
  layer.style.backgroundImage = `url("${tiles[frame]}")`;
}

function tick(now) {
  raf = requestAnimationFrame(tick);
  // Il rAF gira a frequenza di schermo, il rumore no: si disegna solo quando
  // è passato il passo. A 60 fps la grana diventa sfarfallio digitale e perde
  // la qualità analogica che è tutto il motivo per cui è lì.
  if (now - last < STEP) return;
  last = now;
  frame = (frame + 1) % tiles.length;
  draw();
}

function stop() {
  if (raf) cancelAnimationFrame(raf);
  raf = null;
}

function start() {
  stop();
  if (!layer || !tiles.length) return;

  // Con movimento ridotto la grana **resta**, ferma: è una texture di fondo,
  // e toglierla cambierebbe il tono della pagina invece di calmarla. È il
  // movimento che va tolto, non il grano.
  if (prefersReducedMotion() || document.hidden) {
    draw();
    return;
  }

  last = 0;
  raf = requestAnimationFrame(tick);
}

async function init() {
  if (document.querySelector('.grain')) return;

  layer = document.createElement('div');
  layer.className = 'grain';
  layer.setAttribute('aria-hidden', 'true');

  // Le otto tessere si generano sempre, anche con movimento ridotto: costano
  // una volta sola e già fuori dal cammino critico, e così l'impostazione può
  // cambiare a pagina aperta senza restare senza fotogrammi da alternare.
  tiles = await Promise.all(Array.from({ length: GRAIN.frames }, makeTile));

  document.body.appendChild(layer);
  start();

  // A scheda nascosta non serve a nessuno, ed è un repaint a schermo intero
  // dodici volte al secondo.
  document.addEventListener('visibilitychange', start);

  // Come in `tokens.js`: l'impostazione di sistema può cambiare a sito aperto,
  // e con Swup la pagina non si ricarica mai.
  window.matchMedia?.('(prefers-reduced-motion: reduce)')
    ?.addEventListener?.('change', start);
}

// Otto volte 16.384 pixel casuali più altrettante codifiche PNG, sul main
// thread. Non deve stare nel cammino critico del primo paint: su questo sito
// l'LCP è già stato pagato caro una volta (vedi il gate `is-loading` in
// [[Bug]]), e a 2,5% di opacità comparire qualche centinaio di millisecondi
// dopo non si vede.
function whenIdle(callback) {
  if (typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(callback, { timeout: 2000 });
  } else {
    setTimeout(callback, 500);
  }
}

if (document.readyState === 'complete') {
  whenIdle(init);
} else {
  window.addEventListener('load', () => whenIdle(init), { once: true });
}
