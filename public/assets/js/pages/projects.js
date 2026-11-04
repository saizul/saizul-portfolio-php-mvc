/**
 * projects.js — AI Command Center
 * Projects rendered as glowing AI nodes in layered network.
 * Connections animate with data-flow shimmer.
 * Clicking a node opens the project card overlay dynamically.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-projects');
  if (!canvas) return;

  const THREE = await import('https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js');

  /* ── Renderer ── */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);

  /* ── Scene / Camera ── */
  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
  camera.position.set(0, 0, 12);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.15));
  const centerLight = new THREE.PointLight(0x6ee7b7, 3, 20);
  scene.add(centerLight);

  /* ════════════════════════════════════════════
     PULL PROJECT DATA from DOM (server-rendered)
  ════════════════════════════════════════════ */
  const projectCards = Array.from(document.querySelectorAll('[data-project-id]'));
  const projectData  = projectCards.map(el => ({
    id:    el.dataset.projectId,
    title: el.dataset.projectTitle  || 'Project',
    tech:  el.dataset.projectTech   || '',
    desc:  el.querySelector('.project-desc')?.textContent?.trim() || '',
    url:   el.dataset.projectUrl    || '#',
    el,
  }));

  /* ════════════════════════════════════════════
     LAYERS — AI node network
  ════════════════════════════════════════════ */
  const LAYERS     = [3, 5, 6, 5, 3];
  const LAYER_X    = [-5.5, -2.8, 0, 2.8, 5.5];
  const allNodes   = [];
  const allLines   = [];
  const layerNodes = [];

  const coreNodeGeo = new THREE.SphereGeometry(0.18, 12, 12);
  const coreNodeMat = new THREE.MeshBasicMaterial({ color: 0x6ee7b7 });

  LAYERS.forEach((count, li) => {
    const lNodes = [];
    for (let ni = 0; ni < count; ni++) {
      const y = (ni - (count - 1) / 2) * 1.4;

      // Is there a real project mapped to this node?
      const projIdx = allNodes.length;
      const proj    = projectData[projIdx] || null;

      const color = proj ? 0xc0392b : 0x6ee7b7;
      const mat   = new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.85 });
      const node  = new THREE.Mesh(
        proj ? new THREE.SphereGeometry(0.24, 14, 14) : coreNodeGeo,
        mat
      );
      node.position.set(LAYER_X[li], y, 0);
      node.userData = { phase: Math.random() * Math.PI * 2, layerIdx: li, project: proj };

      // Halo for project nodes
      if (proj) {
        const halo = new THREE.Mesh(
          new THREE.RingGeometry(0.3, 0.44, 24),
          new THREE.MeshBasicMaterial({ color: 0xc0392b, side: THREE.DoubleSide, transparent: true, opacity: 0.3 })
        );
        halo.position.copy(node.position);
        scene.add(halo);
        node.userData.halo = halo;
      }

      scene.add(node);
      lNodes.push(node);
      allNodes.push(node);
    }
    layerNodes.push(lNodes);
  });

  // Connections between adjacent layers
  for (let li = 1; li < LAYERS.length; li++) {
    layerNodes[li].forEach(b => {
      layerNodes[li - 1].forEach(a => {
        const pts = [a.position.clone(), b.position.clone()];
        const geo  = new THREE.BufferGeometry().setFromPoints(pts);
        const line = new THREE.Line(geo, new THREE.LineBasicMaterial({ color: 0x6ee7b7, transparent: true, opacity: 0.06 }));
        scene.add(line);
        allLines.push({ line, nodeA: a, nodeB: b });
      });
    });
  }

  /* ════════════════════════════════════════════
     DATA FLOW PARTICLES along connections
  ════════════════════════════════════════════ */
  const flowParticles = [];
  allLines.forEach(({ line, nodeA, nodeB }) => {
    if (Math.random() > 0.4) return; // sparse
    const dot = new THREE.Mesh(
      new THREE.SphereGeometry(0.05, 6, 6),
      new THREE.MeshBasicMaterial({ color: 0x6ee7b7, transparent: true, opacity: 0.8 })
    );
    dot.userData = { nodeA, nodeB, t: Math.random(), speed: 0.4 + Math.random() * 0.6 };
    scene.add(dot);
    flowParticles.push(dot);
  });

  /* ── Background star field ── */
  const starGeo = new THREE.BufferGeometry();
  const starPos = new Float32Array(1500 * 3);
  for (let i = 0; i < 1500; i++) {
    starPos[i*3]   = (Math.random() - 0.5) * 40;
    starPos[i*3+1] = (Math.random() - 0.5) * 25;
    starPos[i*3+2] = (Math.random() - 0.5) * 10 - 5;
  }
  starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
  scene.add(new THREE.Points(starGeo, new THREE.PointsMaterial({ color: 0x6ee7b7, size: 0.03, transparent: true, opacity: 0.25, blending: THREE.AdditiveBlending, depthWrite: false })));

  /* ════════════════════════════════════════════
     PROJECT CARD OVERLAY
  ════════════════════════════════════════════ */
  const overlay = document.createElement('div');
  overlay.style.cssText = `
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);
    z-index:10000;background:rgba(8,12,16,0.96);border:1px solid rgba(192,57,43,0.6);
    padding:32px;border-radius:16px;color:#fff;width:360px;max-width:90vw;
    backdrop-filter:blur(20px);opacity:0;pointer-events:none;
    transition:opacity 0.25s,transform 0.25s;
  `;
  overlay.innerHTML = `
    <button id="cmd-close" style="position:absolute;top:12px;right:16px;background:none;border:none;color:#fff;font-size:20px;cursor:pointer;opacity:0.6">&times;</button>
    <div id="cmd-content"></div>
  `;
  document.body.appendChild(overlay);

  overlay.querySelector('#cmd-close').addEventListener('click', closeOverlay);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeOverlay(); });

  function openOverlay(proj) {
    overlay.querySelector('#cmd-content').innerHTML = `
      <div style="color:#c0392b;font-size:11px;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px">AI Project</div>
      <h3 style="font-size:20px;font-weight:700;margin-bottom:10px">${proj.title}</h3>
      <p style="color:#94a3b8;font-size:13px;line-height:1.6;margin-bottom:14px">${proj.desc || 'Cutting-edge AI project.'}</p>
      <div style="color:#6ee7b7;font-size:12px;margin-bottom:16px">${proj.tech}</div>
      <a href="${proj.url}" style="display:inline-block;background:#c0392b;color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600">View Project →</a>
    `;
    overlay.style.opacity = '1';
    overlay.style.transform = 'translate(-50%,-50%) scale(1)';
    overlay.style.pointerEvents = 'auto';
  }

  function closeOverlay() {
    overlay.style.opacity = '0';
    overlay.style.transform = 'translate(-50%,-50%) scale(0.9)';
    overlay.style.pointerEvents = 'none';
  }

  /* ── Scroll camera ── */
  let scrollProg = 0;
  if (!prefersReduced && gsap && ScrollTrigger) {
    ScrollTrigger.create({
      trigger: document.body, start: 'top top', end: 'bottom bottom',
      onUpdate: s => { scrollProg = s.progress; }
    });
  }

  /* ── Mouse ── */
  let mx = 0, my = 0;
  const pointer = new THREE.Vector2();
  window.addEventListener('mousemove', e => {
    mx = (e.clientX / window.innerWidth  - 0.5) * 2;
    my = (e.clientY / window.innerHeight - 0.5) * 2;
    pointer.x = mx;
    pointer.y = -my;
  }, { passive: true });

  /* ── Raycaster ── */
  const raycaster = new THREE.Raycaster();
  const projectNodes = allNodes.filter(n => n.userData.project);
  canvas.addEventListener('click', () => {
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(projectNodes);
    if (hits.length > 0) openOverlay(hits[0].object.userData.project);
  });

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

    // Node pulse
    allNodes.forEach(n => {
      const wave = Math.sin(t * 1.4 - n.userData.layerIdx * 0.8 + n.userData.phase);
      n.material.opacity = 0.55 + Math.max(0, wave) * 0.4;
      n.scale.setScalar(0.9 + Math.max(0, wave) * 0.2);
      if (n.userData.halo) {
        n.userData.halo.material.opacity = 0.08 + Math.max(0, wave) * 0.28;
        n.userData.halo.scale.setScalar(1 + Math.abs(wave) * 0.3);
      }
    });

    // Connection shimmer
    allLines.forEach(({ line }, i) => {
      line.material.opacity = 0.04 + Math.abs(Math.sin(t * 0.9 + i * 0.4)) * 0.12;
    });

    // Flow particles
    flowParticles.forEach(p => {
      p.userData.t = (p.userData.t + p.userData.speed * 0.008) % 1;
      p.position.lerpVectors(p.userData.nodeA.position, p.userData.nodeB.position, p.userData.t);
      p.material.opacity = Math.sin(p.userData.t * Math.PI) * 0.9;
    });

    // Camera
    camera.position.x += (mx * 1.2 - camera.position.x) * 0.04;
    camera.position.y += (-my * 0.8 - scrollProg * 1.5 - camera.position.y) * 0.04;
    camera.lookAt(0, 0, 0);

    centerLight.position.x = Math.sin(t * 0.4) * 3;
    centerLight.position.y = Math.cos(t * 0.3) * 2;

    renderer.render(scene, camera);
  }

  animate();
}
