import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { splitLines, groupByRows } from './helpers';

gsap.registerPlugin(ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
  window.addEventListener('load', () => {
    const lenis = new Lenis({ autoRaf: true });

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
      lenis.raf(time * 1000);
    });
    gsap.ticker.lagSmoothing(0);

    // home + archive: animate project rows
    const projects = gsap.utils.toArray('.project');
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

    // single page: animate flex-row elements
    gsap.utils.toArray('.single .flex-row').forEach((row) => {
      gsap.from(row.querySelectorAll('.element'), {
        yPercent: 20,
        skewY: 3,
        opacity: 0,
        duration: 1.2,
        ease: 'power3.out',
        stagger: 0.5,
        scrollTrigger: { trigger: row, start: 'top 85%', once: false },
      });
    });

    // single page: animate text elements
    gsap.utils.toArray('.single .flex-row').forEach((row) => {
      gsap.from(row.querySelectorAll('.text-element'), {
        yPercent: 20,
        opacity: 0,
        duration: 1.2,
        ease: 'power3.out',
        stagger: 0.15,
        scrollTrigger: { trigger: row, start: 'top 85%', once: false },
      });
    });

    // split + animate text lines
    gsap.utils.toArray('.text-element-lines').forEach((el) => {
      splitLines(el);
      gsap.from(el.querySelectorAll('.line'), {
        yPercent: 110,
        opacity: 0,
        duration: 1,
        ease: 'power3.out',
        stagger: 0.15,
        scrollTrigger: { trigger: el, start: 'top 85%', once: true },
      });
    });

    // about page
    gsap.from('#info-list .text-element, #clients-list .text-element', {
      yPercent: 20,
      opacity: 0,
      duration: 1.2,
      ease: 'power3.out',
      stagger: 0.15,
      scrollTrigger: { trigger: '#infos', start: 'top 85%', once: false },
    });
  });

  window.addEventListener('resize', () => {
    document.querySelectorAll('.text-element-lines').forEach(el => splitLines(el));
    ScrollTrigger.refresh();
  });
});
