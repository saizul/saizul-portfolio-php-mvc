/**
 * portfolio.js — Interactive Project City
 * Portfolio projects rendered as glowing 3D buildings in a city grid.
 * Hover reveals project name, tech, results.
 * Click opens project detail overlay.
 * Smooth GSAP fly-in entrance.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-portfolio');
  if (!canvas) return;

  const THREE = await import('https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js');

  /* ── Renderer ── */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);

  /* ── Scene / Camera ── */
  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 150);
  camera.position.set(0, 10, 18);
  camera.lookAt(0, 0, 0);

  scene.fog = new THREE.FogExp2(0x040810, 0.03);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0x0d1b2a, 0.5));
  const crimsonKey = new THREE.DirectionalLight(0xc0392b, 1.5);
  crimsonKey.position.set(-5, 8, 5);
  scene.add(crimsonKey);
  const tealFill = new THREE.DirectionalLight(0x6ee7b7, 0.8);
  tealFill.position.set(5, 4, -3);
  scene.add(tealFill);

  /* ════════════════════════════════════════════
     PULL PROJECT DATA from DOM
  ════════════════════════════════════════════ */
  const portEls = Array.from(document.querySelectorAll('[data-portfolio-id]'));
  const PROJECTS = portEls.length > 0 ? portEls.map(el => ({
    title:  el.dataset.portfolioTitle  || 'Project',
    tech:   el.dataset.portfolioTech   || '',
    result: el.dataset.portfolioResult || '',
    url:    el.dataset.portfolioUrl    || '#',
    type:   el.dataset.portfolioType   || 'web',
  })) : [
    { title: 'AI Chatbot Platform',    tech: 'Python, LangChain, React',  result: '10x faster support', type: 'ai'  },
    { title: 'E-commerce SEO Suite',   tech: 'PHP, MySQL, GSAP',          result: '300% traffic growth', type: 'seo' },
    { title: 'NGO Digital Revamp',     tech: 'WordPress, Three.js',       result: '50k+ monthly users',  type: 'web' },
    { title: 'Gov Portal System',      tech: 'PHP MVC, MySQL',            result: '99.9% uptime',        type: 'gov' },
    { title: 'ML Document Parser',     tech: 'Python, TensorFlow',        result: '95% accuracy',        type: 'ai'  },
    { title: 'React Dashboard',        tech: 'React, Node.js, D3',        result: 'Real-time analytics', type: 'web' },
    { title: 'UK Client SEO',          tech: 'Technical SEO, Schema',     result: '#1 rankings',         type: 'seo' },
    { title: 'Inventory System',       tech: 'PHP, MySQL, AJAX',          result: '60% time saved',      type: 'web' },
  ];

  /* ── Building color by type ── */
  const typeColors = {
    ai:  0xc0392b,
    seo: 0xa78bfa,
    web: 0x6ee7b7,
    gov: 0x7dd3fc,
  };

  /* ── City grid ── */
  const COLS  = 4;
  const ROWS  = Math.ceil(PROJECTS.length / COLS);
  const GAP   = 3.2;
  const buildings = [];

  /* ── Ground plane ── */
  const groundGeo = new THREE.PlaneGeometry(50, 50);
  const groundMat = new THREE.MeshBasicMaterial({ color: 0x040810 });
  const ground    = new THREE.Mesh(groundGeo, groundMat);
  ground.rotation.x = -Math.PI / 2;
  ground.position.y = -0.01;
  scene.add(ground);

  // Grid lines on ground
  const gridHelper = new THREE.GridHelper(50, 25, 0x1a2f35, 0x0d1b2a);
  scene.add(gridHelper);

  PROJECTS.forEach((proj, i) => {
    const col = i % COLS;
    const row = Math.floor(i / COLS);
    const x   = (col - (COLS - 1) / 2) * GAP;
    const z   = (row - (ROWS - 1) / 2) * GAP;
    const h   = 0.8 + Math.random() * 2.4;
    const color = typeColors[proj.type] || 0x6ee7b7;

    // Building base
    const bGeo  = new THREE.BoxGeometry(1.4, h, 1.4);
    const bMat  = new THREE.MeshBasicMaterial({ color: 0x0d1b2a, transparent: true, opacity: 0.9 });
    const building = new THREE.Mesh(bGeo, bMat);
    building.position.set(x, h / 2, z);
    building.userData = { project: proj, origH: h, origY: h / 2, x, z };
    scene.add(building);
    buildings.push(building);

    // Neon edges
    const edgesGeo = new THREE.EdgesGeometry(bGeo);
    const edgesMat = new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.6 });
    const edges    = new THREE.LineSegments(edgesGeo, edgesMat);
    building.add(edges);

    // Rooftop glow
    const roofGeo = new THREE.PlaneGeometry(1.2, 1.2);
    const roofMat = new THREE.MeshBasicMaterial({ color, side: THREE.DoubleSide, transparent: true, opacity: 0.25 });
    const roof    = new THREE.Mesh(roofGeo, roofMat);
    roof.rotation.x = -Math.PI / 2;
    roof.position.set(0, h / 2 + 0.01, 0);
    building.add(roof);
    building.userData.roofMat = roofMat;
    building.userData.edgesMat = edgesMat;
    building.userData.color = color;

    // Rooftop antenna
    const antGeo = new THREE.CylinderGeometry(0.02, 0.02, 0.4, 4);
    const antMat = new THREE.MeshBasicMaterial({ color });
    const ant    = new THREE.Mesh(antGeo, antMat);
    ant.position.set(0, h / 2 + 0.2, 0);
    building.add(ant);
  });

  /* ── Ambient city particles ── */
  const cityGeo = new THREE.BufferGeometry();
  const cityPos = new Float32Array(800 * 3);
  for (let i = 0; i < 800; i++) {
    cityPos[i*3]   = (Math.random() - 0.5) * 20;
    cityPos[i*3+1] = Math.random() * 8;
    cityPos[i*3+2] = (Math.random() - 0.5) * 20;
  }
  cityGeo.setAttribute('position', new THREE.BufferAttribute(cityPos, 3));
  scene.add(new THREE.Points(cityGeo, new THREE.PointsMaterial({ color: 0x6ee7b7, size: 0.05, transparent: true, opacity: 0.25, blending: THREE.AdditiveBlending, depthWrite: false })));

  /* ── Project detail overlay ── */
  const overlay = document.createElement('div');
  overlay.style.cssText = `
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.92);
    z-index:10000;background:rgba(8,12,16,0.96);border:1px solid rgba(192,57,43,0.5);
    padding:30px;border-radius:16px;color:#fff;width:380px;max-width:92vw;
    backdrop-filter:blur(20px);opacity:0;pointer-events:none;transition:all 0.22s;
  `;
  overlay.innerHTML = `
    <button id="city-close" style="position:absolute;top:12px;right:16px;background:none;border:none;color:#fff;font-size:20px;cursor:pointer;opacity:0.6">&times;</button>
    <div id="city-content"></div>
  `;
  document.body.appendChild(overlay);
  overlay.querySelector('#city-close').addEventListener('click', () => {
    overlay.style.opacity = '0'; overlay.style.pointerEvents = 'none';
  });

  /* ── Tooltip ── */
  const tooltip = document.createElement('div');
  tooltip.style.cssText = `
    position:fixed;pointer-events:none;z-index:9999;
    background:rgba(8,12,16,0.92);border:1px solid rgba(110,231,183,0.4);
    padding:8px 14px;border-radius:8px;color:#fff;font-size:12px;line-height:1.5;
    backdrop-filter:blur(8px);opacity:0;transition:opacity 0.15s;
  `;
  document.body.appendChild(tooltip);

  /* ── Mouse / raycaster ── */
  const pointer   = new THREE.Vector2();
  const raycaster = new THREE.Raycaster();
  let hoveredBuilding = null;
  let mx = 0;

  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / window.innerWidth)  * 2 - 1;
    pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
    mx = (e.clientX / window.innerWidth - 0.5) * 2;
    tooltip.style.left = (e.clientX + 16) + 'px';
    tooltip.style.top  = (e.clientY - 10) + 'px';
  }, { passive: true });

  canvas.addEventListener('click', () => {
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(buildings);
    if (!hits.length) return;
    const proj = hits[0].object.userData.project;
    overlay.querySelector('#city-content').innerHTML = `
      <div style="color:#c0392b;font-size:10px;letter-spacing:2px;text-transform:uppercase;margin-bottom:6px">${proj.type?.toUpperCase() || 'PROJECT'}</div>
      <h3 style="font-size:20px;font-weight:700;margin-bottom:8px">${proj.title}</h3>
      <div style="color:#6ee7b7;font-size:13px;margin-bottom:10px">${proj.tech}</div>
      <div style="color:#94a3b8;font-size:13px;margin-bottom:16px">Results: <span style="color:#fff">${proj.result}</span></div>
      <a href="${proj.url}" style="display:inline-block;background:#c0392b;color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600">View Project →</a>
    `;
    overlay.style.opacity = '1';
    overlay.style.transform = 'translate(-50%,-50%) scale(1)';
    overlay.style.pointerEvents = 'auto';
  });

  /* ── Scroll ── */
  let scrollProg = 0;
  if (!prefersReduced && gsap && ScrollTrigger) {
    ScrollTrigger.create({
      trigger: document.body, start: 'top top', end: 'bottom bottom',
      onUpdate: s => { scrollProg = s.progress; }
    });
  }

  /* ── GSAP entrance ── */
  if (!prefersReduced && gsap) {
    buildings.forEach((b, i) => {
      const origY = b.position.y;
      b.position.y = -5;
      gsap.to(b.position, { y: origY, duration: 1, ease: 'power3.out', delay: i * 0.06 });
    });
  }

  /* ── Resize ── */
  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });

  if (prefersReduced) { renderer.render(scene, camera); return; }

  const clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();

    // Camera orbit based on scroll + mouse
    const camAngle = scrollProg * Math.PI * 0.6 + mx * 0.15;
    const camRadius = 18 - scrollProg * 4;
    camera.position.x = Math.sin(camAngle) * camRadius;
    camera.position.z = Math.cos(camAngle) * camRadius;
    camera.position.y = 10 - scrollProg * 4;
    camera.lookAt(0, 0, 0);

    // Raycaster hover
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(buildings);

    if (hits.length > 0) {
      const hit = hits[0].object;
      if (hoveredBuilding !== hit) {
        if (hoveredBuilding) {
          hoveredBuilding.userData.edgesMat.opacity = 0.6;
          hoveredBuilding.userData.roofMat.opacity  = 0.25;
          hoveredBuilding.scale.set(1, 1, 1);
        }
        hoveredBuilding = hit;
      }
      hit.userData.edgesMat.opacity = 1.0;
      hit.userData.roofMat.opacity  = 0.55 + Math.sin(t * 3) * 0.15;
      hit.scale.set(1, 1.08 + Math.sin(t * 2) * 0.04, 1);

      const p = hit.userData.project;
      tooltip.innerHTML = `<strong>${p.title}</strong><br><span style="color:#6ee7b7">${p.tech}</span>`;
      tooltip.style.opacity = '1';
      canvas.style.cursor = 'pointer';
    } else {
      if (hoveredBuilding) {
        hoveredBuilding.userData.edgesMat.opacity = 0.6;
        hoveredBuilding.userData.roofMat.opacity  = 0.25;
        hoveredBuilding.scale.set(1, 1, 1);
        hoveredBuilding = null;
      }
      tooltip.style.opacity = '0';
      canvas.style.cursor = '';
    }

    // Roof flicker on all
    buildings.forEach((b, i) => {
      if (b === hoveredBuilding) return;
      b.userData.roofMat.opacity = 0.12 + Math.abs(Math.sin(t * 1.5 + i * 0.7)) * 0.12;
    });

    renderer.render(scene, camera);
  }

  animate();
}
