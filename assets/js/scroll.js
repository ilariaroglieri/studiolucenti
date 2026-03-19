// wait until DOM is ready
document.addEventListener("DOMContentLoaded", function(event){

  //wait until images, links, fonts, stylesheets, and js is loaded
  window.addEventListener("load", function(e){

    const lenis = new Lenis({
      autoRaf: true,
    });

    // Synchronize Lenis scrolling with GSAP's ScrollTrigger plugin
    lenis.on('scroll', ScrollTrigger.update);

    // Add Lenis's requestAnimationFrame (raf) method to GSAP's ticker
    // This ensures Lenis's smooth scroll animation updates on each GSAP tick
    gsap.ticker.add((time) => {
      lenis.raf(time * 1000); // Convert time from seconds to milliseconds
    });

  // Disable lag smoothing in GSAP to prevent any delay in scroll animations
    gsap.ticker.lagSmoothing(0);

    // home + archive
    const projects = gsap.utils.toArray('.project');
    const rows = groupByRows(projects);

    rows.forEach((row) => {
      gsap.from(row.items, {
        yPercent: 20,
        skewY: 3,
        opacity: 0,
        duration: 1.2,
        ease: "power3.out",
        stagger: 0.5,
        scrollTrigger: {
          trigger: row.items[0],
          start: "top 85%",
          once: false
        }
      });
    });

    // single page
    gsap.utils.toArray('.single .flex-row').forEach((row) => {
  
      const items = row.querySelectorAll('.element');

      gsap.from(items, {
        yPercent: 20,
        skewY: 3,
        opacity: 0,
        duration: 1.2,
        ease: "power3.out",
        stagger: 0.5,
        scrollTrigger: {
          trigger: row,
          start: "top 85%",
          once: false
        }
      });

    });

    // single page text element
    gsap.utils.toArray('.single .flex-row').forEach((row) => {
  
      const items = row.querySelectorAll('.text-element');

      gsap.from(items, {
        yPercent: 20,
        opacity: 0,
        duration: 1.2,
        ease: "power3.out",
        stagger: 0.15,
        scrollTrigger: {
          trigger: row,
          start: "top 85%",
          once: false
        }
      });

    });

    gsap.utils.toArray('.text-element-lines').forEach((el) => {
      splitLines(el);

      const lines = el.querySelectorAll('.line');

      gsap.from(lines, {
        yPercent: 110,
        opacity: 0,
        duration: 1,
        ease: "power3.out",
        stagger: 0.15,
        scrollTrigger: {
          trigger: el,
          start: "top 85%",
          once: true
        }
      });
    });

    // about
    gsap.from('#info-list .text-element, #clients-list .text-element', {
      yPercent: 20,
      opacity: 0,
      duration: 1.2,
      ease: "power3.out",
      stagger: 0.15,
      scrollTrigger: {
        trigger: '#infos',
        start: "top 85%",
        once: false
      }
    });


  }, false);

  window.addEventListener('resize', () => {
    document.querySelectorAll('.text-element-lines').forEach(el => {
      splitLines(el);
    });
    ScrollTrigger.refresh();
  });

});
