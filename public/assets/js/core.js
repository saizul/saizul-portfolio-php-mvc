/**
 * core.js — Global animation engine
 * Initialises: Lenis smooth scroll, GSAP + ScrollTrigger,
 * page-specific 3D scene lazy loader, global reveals.
 * Loaded as <script type="module"> on every public page.
 */

const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const gsap = window.gsap || null;
const ST   = window.ScrollTrigger || null;
if (gsap && ST) gsap.registerPlugin(ST);

/* ── Lenis ── */
let lenis = null;
if (!prefersReduced) {
  (async () => {
    try {
      const { default: Lenis } = await import('https://cdn.jsdelivr.net/npm/lenis@1.1.10/dist/lenis.mjs');
      lenis = new Lenis({ duration: 1.2, easing: t => Math.min(1, 1.001 - Math.pow(2, -10*t)), smoothWheel: true, touchMultiplier: 1.5 });
      if (gsap) { gsap.ticker.add(time => lenis.raf(time * 1000)); gsap.ticker.lagSmoothing(0); }
      else { const tick = ts => { lenis.raf(ts); requestAnimationFrame(tick); }; requestAnimationFrame(tick); }
      if (ST) lenis.on('scroll', ST.update);
      window.__lenis = lenis;
    } catch(e) {}
  })();
}

/* ── Nav ── */
const nav = document.querySelector('.nav');
if (nav) window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 40), { passive: true });

/* ── Mobile nav ── */
const hamburger = document.querySelector('.nav-hamburger');
const mobileNav = document.querySelector('.nav-mobile');
if (hamburger && mobileNav) {
  hamburger.addEventListener('click', () => {
    const open = mobileNav.classList.toggle('open');
    hamburger.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
  });
  mobileNav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    mobileNav.classList.remove('open'); hamburger.setAttribute('aria-expanded','false'); document.body.style.overflow='';
  }));
}

/* ── Scroll reveals ── */
if (!prefersReduced && gsap && ST) {
  gsap.utils.toArray('.reveal').forEach(el => gsap.from(el, { opacity:0, y:44, duration:0.85, ease:'power3.out', scrollTrigger:{trigger:el,start:'top 88%',once:true} }));
  gsap.utils.toArray('.reveal-left').forEach(el => gsap.from(el, { opacity:0, x:-50, duration:0.9, ease:'power3.out', scrollTrigger:{trigger:el,start:'top 88%',once:true} }));
  gsap.utils.toArray('.reveal-stagger').forEach(p => {
    gsap.from(p.querySelectorAll(':scope > *'), { opacity:0, y:30, duration:0.7, ease:'power3.out', stagger:0.09, scrollTrigger:{trigger:p,start:'top 85%',once:true} });
  });
  document.querySelectorAll('.skill-bar-fill[data-pct]').forEach(bar => {
    gsap.fromTo(bar, {width:'0%'}, {width:bar.dataset.pct+'%', duration:1.3, ease:'power2.out', scrollTrigger:{trigger:bar,start:'top 90%',once:true}});
  });
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count,10), suffix = el.dataset.suffix||'', obj={v:0};
    gsap.to(obj,{v:target,duration:2,ease:'power2.out',onUpdate(){el.textContent=Math.round(obj.v)+suffix;},scrollTrigger:{trigger:el,start:'top 85%',once:true}});
  });
} else {
  const io = new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target);}}),{threshold:0.12});
  document.querySelectorAll('.reveal,.reveal-left').forEach(el=>io.observe(el));
  const bIo = new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.style.width=e.target.dataset.pct+'%';bIo.unobserve(e.target);}}),{threshold:0.3});
  document.querySelectorAll('.skill-bar-fill[data-pct]').forEach(b=>bIo.observe(b));
}

/* ── Custom cursor ── */
const cursorDot = document.querySelector('.cursor-dot');
if (cursorDot && !prefersReduced && window.matchMedia('(pointer:fine)').matches) {
  let mx=-100,my=-100,cx=-100,cy=-100;
  document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;},{passive:true});
  const mv=()=>{cx+=(mx-cx)*0.18;cy+=(my-cy)*0.18;cursorDot.style.transform=`translate(${cx-4}px,${cy-4}px)`;requestAnimationFrame(mv);};
  requestAnimationFrame(mv);
  document.querySelectorAll('a,button,[data-cursor]').forEach(el=>{
    el.addEventListener('mouseenter',()=>cursorDot.classList.add('hover'));
    el.addEventListener('mouseleave',()=>cursorDot.classList.remove('hover'));
  });
}

/* ── Lazy-load 3D scene ── */
const pageScript = document.body.dataset.page;
if (pageScript) {
  const loadScene = async () => {
    try {
      const mod = await import(`/assets/js/pages/${pageScript}.js`);
      if (mod && typeof mod.init === 'function') mod.init({ gsap, ScrollTrigger: ST, lenis });
    } catch(err) {}
  };
  const sentinel = document.querySelector('[id^="canvas-"]');
  if (sentinel) {
    const sIo = new IntersectionObserver(es=>{if(es[0].isIntersecting){sIo.disconnect();loadScene();}},{rootMargin:'300px'});
    sIo.observe(sentinel);
  } else if ('requestIdleCallback' in window) requestIdleCallback(loadScene,{timeout:2000});
  else setTimeout(loadScene,400);
}

export { lenis, gsap, ST as ScrollTrigger };
