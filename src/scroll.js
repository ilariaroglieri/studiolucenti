import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import { groupByRows } from './helpers';

gsap.registerPlugin(ScrollTrigger, SplitText);

document.addEventListener('DOMContentLoaded', () => {
  window.addEventListener('load', () => {
    const lenis = new Lenis({ autoRaf: true });

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
      lenis.raf(time * 1000);
    });
    gsap.ticker.lagSmoothing(0);

    document.fonts.ready.then(() => {
      const body = document.body;

      // home + archive: animate project rows
      if (body.classList.contains('home') || body.classList.contains('blog')) {
        const projects = gsap.utils.toArray('.project');
        if (projects.length) {
          const rows = groupByRows(projects);
          rows.forEach((row) => {
            gsap.from(row.items, {
              yPercent: 20,
              skewY: 3,
              opacity: 0,
              duration: 1.2,
              ease: 'power3.out',
              stagger: 0.5,
              scrollTrigger: {
                trigger: row.items[0],
                start: 'top 85%',
                once: false,
              },
            });
          });
        }
      }

      // single page: animate flex-row elements + text elements
      if (body.classList.contains('single')) {
        gsap.utils.toArray('.single .flex-row').forEach((row) => {
          const elements = row.querySelectorAll('.element');
          if (elements.length) {
            gsap.from(elements, {
              yPercent: 20,
              skewY: 3,
              opacity: 0,
              duration: 1.2,
              ease: 'power3.out',
              stagger: 0.5,
              scrollTrigger: { trigger: row, start: 'top 85%', once: false },
            });
          }

          const textElements = row.querySelectorAll('.text-element');
          if (textElements.length) {
            gsap.from(textElements, {
              yPercent: 20,
              opacity: 0,
              duration: 1.2,
              ease: 'power3.out',
              stagger: 0.15,
              scrollTrigger: { trigger: row, start: 'top 85%', once: false },
            });
          }
        });
      }

      // split + animate text lines (any page)
      gsap.utils.toArray('.text-element-lines').forEach((el) => {
        const split = SplitText.create(el, {
          type: 'lines',
          linesClass: 'line++',
          mask: 'lines',
        });

        if (!split.lines.length) return;

        gsap.from(split.lines, {
          yPercent: 100,
          autoAlpha: 0,
          duration: 1,
          ease: 'power3.out',
          stagger: 0.1,
          scrollTrigger: { trigger: el, start: 'top 85%', once: true },
          onComplete: () => { split.revert(); },
        });
      });

      // about page
      const infos = document.querySelector('#infos');
      if (infos) {
        gsap.from('#info-list .text-element, #clients-list .text-element', {
          yPercent: 20,
          opacity: 0,
          duration: 1.2,
          ease: 'power3.out',
          stagger: 0.15,
          scrollTrigger: { trigger: infos, start: 'top 85%', once: false },
        });
      }

      ScrollTrigger.refresh();
    });
  });

  window.addEventListener('resize', () => {
    ScrollTrigger.refresh();
  });
});
