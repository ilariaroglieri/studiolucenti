import { prefersReducedMotion } from './tokens';

// =============================
// Orologio di Milano
// =============================
// Sta nel rail del footer, cioè nella prima fascia che si scopre scrollando, ed
// è l'unica cosa che si muove in quella schermata: [[Design system]] concede
// un'animazione protagonista per schermata, e qui è questa.
//
// Il footer è fuori dal container di Swup (`.content`): sopravvive alle
// navigazioni, quindi si avvia una volta sola e non va reinizializzato.

const TIME_ZONE = 'Europe/Rome';

let clock = null;
let format = null;
let formatWasReduced = null;
let timer = null;

// `hourCycle: 'h23'` e non `hour12: false`: il secondo, su alcuni motori,
// stampa mezzanotte come "24:00".
function formatterFor(reduced) {
  return new Intl.DateTimeFormat('en-GB', {
    timeZone: TIME_ZONE,
    hourCycle: 'h23',
    hour: '2-digit',
    minute: '2-digit',
    ...(reduced ? {} : { second: '2-digit' }),
  });
}

// Con movimento ridotto l'orologio resta — è informazione, non decorazione — ma
// scende al minuto: un testo che si riscrive da solo ogni secondo è contenuto
// in movimento a tutti gli effetti.
function render() {
  const reduced = prefersReducedMotion();

  if (format === null || formatWasReduced !== reduced) {
    format = formatterFor(reduced);
    formatWasReduced = reduced;
  }

  const value = format.format(new Date());
  if (clock.textContent !== value) {
    clock.textContent = value;
    clock.setAttribute('datetime', value);
  }

  return reduced;
}

// Riagganciato al confine del secondo a ogni giro: con un `setInterval` fisso la
// deriva si accumula e prima o poi un tick salta o si ripete, che su cifre
// tabulari ferme si vede.
function schedule() {
  clearTimeout(timer);

  const step = render() ? 60000 : 1000;
  timer = setTimeout(schedule, step - (Date.now() % step));
}

function stop() {
  clearTimeout(timer);
  timer = null;
}

function start() {
  clock = document.getElementById('footer-clock');
  // Senza campo email il footer stampa comunque il rail: se manca l'elemento,
  // manca il footer, e non c'è niente da aggiornare.
  if (!clock) return;

  schedule();

  // A scheda nascosta il timer non lo guarda nessuno: si ferma e si
  // risincronizza al ritorno, invece di ricucire i tick persi.
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stop();
    else schedule();
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start);
} else {
  start();
}
