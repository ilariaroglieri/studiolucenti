import { gsap } from 'gsap';
import { CustomEase } from 'gsap/CustomEase';

gsap.registerPlugin(CustomEase);

// =============================
// TOKEN — gemello JS di `assets/css/_tokens.scss`
// =============================
// GSAP non legge le CSS custom property: i valori vanno duplicati qui.
// Se cambia un numero di là, cambia anche qui. Non esistono altre durate
// né altri easing nel tema.

// ── Easing ────────────────────────────────────────────────
// Ponte esatto, non approssimato. Una cubic-bezier(x1,y1,x2,y2) è la stessa
// curva della path `M0,0 C x1,y1 x2,y2 1,1`: CustomEase la ricostruisce
// identica al CSS. I `power*` di GSAP sarebbero solo somiglianti, e la
// differenza si vede quando lo stesso gesto passa da CSS a JS.
CustomEase.create('lucenti-out',   'M0,0 C0.16,1 0.3,1 1,1');    // --ease-out
CustomEase.create('lucenti-inout', 'M0,0 C0.65,0 0.35,1 1,1');   // --ease-inout
CustomEase.create('lucenti-quick', 'M0,0 C0.4,0 0.2,1 1,1');     // --ease-quick

export const EASE = {
  out: 'lucenti-out',
  inOut: 'lucenti-inout',
  quick: 'lucenti-quick',
};

// ── Durate ────────────────────────────────────────────────
// In secondi: è l'unità di GSAP. I millisecondi stanno nel CSS.
export const DUR = {
  micro: 0.16,  // --dur-micro
  base: 0.6,    // --dur-base
  page: 1,      // --dur-page
};

// ── Stagger ───────────────────────────────────────────────
// Non è un token di [[Design system]], ma senza un valore condiviso ricompare
// il caso di prima: `stagger: 0.5` faceva partire il quarto progetto un
// secondo e mezzo dopo il primo, e la griglia leggeva come lenta, non come
// composta. Sotto i 100ms lo scaglionamento si sente come una sola entrata.
export const STAGGER = {
  base: 0.06,   // elementi di una riga, blocchi di testo
  words: 0.04,  // reveal tipografico per parole
};

// ── Grana ─────────────────────────────────────────────────
// Il gemello di `--grain-*` in `_tokens.scss`: il tile compare in tutti e due
// i file perché il CSS deve dimensionare lo sfondo e il JS deve disegnare la
// tessera alla stessa misura. Se cambia di là, cambia qui.
// `fps` non ha un gemello CSS: il loop è tutto in JS.
export const GRAIN = {
  tile: 128,    // = --grain-tile
  frames: 8,    // quante tessere si alternano
  fps: 12,      // mai 60: a 60 il rumore diventa sfarfallio digitale
};

// ── prefers-reduced-motion ────────────────────────────────
// Si interroga a ogni chiamata, non una volta sola all'avvio: l'utente può
// cambiare l'impostazione di sistema con il sito già aperto, e con Swup la
// pagina non si ricarica mai.
const reduceQuery =
  typeof window.matchMedia === 'function'
    ? window.matchMedia('(prefers-reduced-motion: reduce)')
    : null;

export function prefersReducedMotion() {
  return !!reduceQuery?.matches;
}
