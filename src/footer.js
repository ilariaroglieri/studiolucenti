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

// =============================
// Email ricomposta
// =============================
// Nel markup l'indirizzo non c'è per intero: PHP lo spezza in due span e la
// chiocciola la mette il CSS, così non resta niente da raccogliere nel
// sorgente — che è l'unica cosa che `antispambot()` non riusciva a garantire,
// perché l'export statico ridecodifica le entità HTML.
//
// Qui si ricompone il testo vero, che si può copiare al contrario di un
// `content` CSS, e si aggiunge l'`href`, che nel markup non c'è affatto.
//
// Gira prima dell'orologio e fuori dalla sua guardia: il footer può esistere
// senza email, e lo shortcode [email] può stare in una pagina qualsiasi.
export function initEmails(root = document) {
  root.querySelectorAll('a.js-email').forEach((el) => {
    const user = el.querySelector('.email-user');
    const domain = el.querySelector('.email-domain');
    if (!user || !domain) return;

    const address = `${user.textContent.trim()}@${domain.textContent.trim()}`;

    el.textContent = address;
    el.href = `mailto:${address}`;
  });
}

function start() {
  initEmails();

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
