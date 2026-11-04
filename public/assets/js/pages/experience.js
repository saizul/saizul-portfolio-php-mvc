/**
 * experience.js — Career Tunnel
 * A 3D tunnel built from TubeGeometry along a CatmullRomCurve3.
 * Scroll moves camera through the tunnel.
 * Each company milestone is a glowing node + interactive card.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-experience');
  if (!canvas) return;

  const THREE = await import('https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js');

  /* ── Renderer ── */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);

  /* ── Scene / Camera ── */
  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.1, 100);

  /* ── Fog for depth ── */
  scene.fog = new THREE.FogExp2(0x040608, 0.07);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.1));
  const spotLight = new THREE.PointLight(0xc0392b, 4, 15);
  scene.add(spotLight);
  const tealLight = new THREE.PointLight(0x6ee7b7, 2, 12);
  scene.add(tealLight);

  /* ════════════════════════════════════════════
     TUNNEL PATH — CatmullRomCurve3
  ════════════════════════════════════════════ */
  const tunnelPath = new THREE.CatmullRomCurve3([
    new THREE.Vector3(0,   0, 0),
    new THREE.Vector3(-2,  1, -8),
    new THREE.Vector3(2,  -1, -16),
    new THREE.Vector3(-1,  2, -24),
    new THREE.Vector3(1,  -1, -32),
    new THREE.Vector3(0,   0, -40),
  ]);

  /* ── Tunnel mesh (inner surface) ── */
  const tunnelGeo = new THREE.TubeGeometry(tunnelPath, 200, 2.2, 10, false);
  const tunnelMat = new THREE.MeshBasicMaterial({
    color: 0x0d1b2a,
    side: THREE.BackSide,
    wireframe: false,
  });
  scene.add(new THREE.Mesh(tunnelGeo, tunnelMat));

  /* ── Tunnel wireframe rings ── */
  const ringsMat = new THREE.MeshBasicMaterial({ color: 0x1a2f35, wireframe: true, transparent: true, opacity: 0.25 });
  const ringsGeo = new THREE.TubeGeometry(tunnelPath, 60, 2.22, 10, false);
  scene.add(new THREE.Mesh(ringsGeo, ringsMat));

  /* ── Tunnel neon edge strips ── */
  const edgePath = tunnelPath.getSpacedPoints(120);
  const offsetPath = edgePath.map(p => p.clone().add(new THREE.Vector3(0, 2.18, 0)));
  const edgeLine = new THREE.Line(
    new THREE.BufferGeometry().setFromPoints(offsetPath),
    new THREE.LineBasicMaterial({ color: 0xc0392b, transparent: true, opacity: 0.4 })
  );
  scene.add(edgeLine);

  const offsetPath2 = edgePath.map(p => p.clone().add(new THREE.Vector3(0, -2.18, 0)));
  scene.add(new THREE.Line(
    new THREE.BufferGeometry().setFromPoints(offsetPath2),
    new THREE.LineBasicMaterial({ color: 0x6ee7b7, transparent: true, opacity: 0.3 })
  ));

  /* ════════════════════════════════════════════
     MILESTONES — pull from DOM
  ════════════════════════════════════════════ */
  const expItems = Array.from(document.querySelectorAll('[data-exp-id]')).map((el, i) => ({
    id:      el.dataset.expId,
    company: el.dataset.expCompany || `Company ${i+1}`,
    role:    el.dataset.expRole    || 'Role',
    period:  el.dataset.expPeriod  || '',
    desc:    el.querySelector('.exp-desc')?.textContent?.trim() || '',
  }));

  // Fallback data if no server items
  const MILESTONES = expItems.length > 0 ? expItems : [
    { company: 'AI Consultancy',   role: 'Lead AI Engineer',    period: '2024–Present' },
    { company: 'Digital Agency',   role: 'Senior Full Stack',   period: '2022–2024'    },
    { company: 'NGO Tech',         role: 'Project Manager',     period: '2020–2022'    },
    { company: 'Startup Hub',      role: 'Junior Developer',    period: '2018–2020'    },
  ];

  const milestoneObjects = [];
  const totalPoints = tunnelPath.getLength();

  MILESTONES.forEach((m, i) => {
    const t = (i + 1) / (MILESTONES.length + 1);
    const pos = tunnelPath.getPointAt(t);
    const tangent = tunnelPath.getTangentAt(t);

    // Node
    const nodeMat = new THREE.MeshBasicMaterial({ color: 0xc0392b, transparent: true, opacity: 0.9 });
    const node    = new THREE.Mesh(new THREE.SphereGeometry(0.25, 14, 14), nodeMat);
    node.position.copy(pos);
    node.userData = { milestone: m, t, haloScale: 1 };
    scene.add(node);
    milestoneObjects.push(node);

    // Halo
    const halo = new THREE.Mesh(
      new THREE.RingGeometry(0.32, 0.5, 24),
      new THREE.MeshBasicMaterial({ color: 0xc0392b, side: THREE.DoubleSide, transparent: true, opacity: 0.25 })
    );
    halo.position.copy(pos);
    halo.lookAt(pos.clone().add(tangent));
    node.userData.halo = halo;
    scene.add(halo);

    // Cross-hairs (tunnel marker)
    const markerH = new THREE.Line(
      new THREE.BufferGeometry().setFromPoints([
        new THREE.Vector3(-0.8, 0, 0).add(pos),
        new THREE.Vector3(0.8,  0, 0).add(pos),
      ]),
      new THREE.LineBasicMaterial({ color: 0xc0392b, transparent: true, opacity: 0.4 })
    );
    const markerV = new THREE.Line(
      new THREE.BufferGeometry().setFromPoints([
        new THREE.Vector3(0, -0.8, 0).add(pos),
        new THREE.Vector3(0,  0.8, 0).add(pos),
      ]),
      new THREE.LineBasicMaterial({ color: 0xc0392b, transparent: true, opacity: 0.4 })
    );
    scene.add(markerH, markerV);
  });

  /* ── Flying particles in tunnel ── */
  const particleGeo = new THREE.BufferGeometry();
  const pCount = 600;
  const pPos = new Float32Array(pCount * 3);
  const pT   = new Float32Array(pCount);
  for (let i = 0; i < pCount; i++) {
    const t = Math.random();
    const p = tunnelPath.getPointAt(t);
    const r = Math.random() * 1.8;
    const a = Math.random() * Math.PI * 2;
    pPos[i*3]   = p.x + Math.cos(a) * r;
    pPos[i*3+1] = p.y + Math.sin(a) * r;
    pPos[i*3+2] = p.z;
    pT[i] = t;
  }
  particleGeo.setAttribute('position', new THREE.BufferAttribute(pPos, 3));
  const particleMat = new THREE.PointsMaterial({ color: 0x6ee7b7, size: 0.05, transparent: true, opacity: 0.35, blending: THREE.AdditiveBlending, depthWrite: false });
  scene.add(new THREE.Points(particleGeo, particleMat));

  /* ── Overlay card ── */
  const overlay = document.createElement('div');
  overlay.style.cssText = `
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);
    z-index:10000;background:rgba(8,12,16,0.95);border:1px solid rgba(192,57,43,0.5);
    padding:28px;border-radius:14px;color:#fff;width:320px;max-width:90vw;
    backdrop-filter:blur(16px);opacity:0;pointer-events:none;transition:all 0.2s;
  `;
  overlay.innerHTML = `
    <button id="exp-close" style="position:absolute;top:10px;right:14px;background:none;border:none;color:#fff;font-size:18px;cursor:pointer;opacity:0.6">&times;</button>
    <div id="exp-content"></div>
  `;
  document.body.appendChild(overlay);
  overlay.querySelector('#exp-close').addEventListener('click', () => {
    overlay.style.opacity = '0'; overlay.style.pointerEvents = 'none';
  });

  /* ── Scroll ── */
  let scrollProg = 0;
  if (!prefersReduced && gsap && ScrollTrigger) {
    ScrollTrigger.create({
      trigger: document.body, start: 'top top', end: 'bottom bottom',
      onUpdate: s => { scrollProg = s.progress; }
    });
  } else {
    window.addEventListener('scroll', () => {
      scrollProg = window.scrollY / Math.max(1, document.body.scrollHeight - window.innerHeight);
    }, { passive: true });
  }

  /* ── Mouse + raycaster ── */
  const pointer = new THREE.Vector2();
  const raycaster = new THREE.Raycaster();
  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / window.innerWidth)  * 2 - 1;
    pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
  }, { passive: true });

  canvas.addEventListener('click', () => {
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(milestoneObjects);
    if (!hits.length) return;
    const m = hits[0].object.userData.milestone;
    overlay.querySelector('#exp-content').innerHTML = `
      <div style="color:#c0392b;font-size:10px;letter-spacing:2px;text-transform:uppercase;margin-bottom:6px">${m.period || ''}</div>
      <h3 style="font-size:18px;font-weight:700;margin-bottom:4px">${m.company}</h3>
      <div style="color:#6ee7b7;font-size:13px;margin-bottom:10px">${m.role}</div>
      <p style="color:#94a3b8;font-size:13px;line-height:1.6">${m.desc || 'Leading initiatives and delivering results.'}</p>
    `;
    overlay.style.opacity = '1';
    overlay.style.transform = 'translate(-50%,-50%) scale(1)';
    overlay.style.pointerEvents = 'auto';
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

    // Camera travels along tunnel based on scroll
    const camT = Math.min(0.98, scrollProg * 0.92);
    const camPos = tunnelPath.getPointAt(camT);
    const lookT  = Math.min(0.99, camT + 0.04);
    const lookAt = tunnelPath.getPointAt(lookT);

    camera.position.lerp(camPos, 0.06);
    camera.lookAt(lookAt);

    // Slight mouse lateral sway
    camera.position.x += (pointer.x * 0.3 - camera.position.x + camPos.x) * 0.03;
    camera.position.y += (-pointer.y * 0.2 - camera.position.y + camPos.y) * 0.03;

    // Lights follow camera
    spotLight.position.copy(camera.position);
    tealLight.position.set(
      camera.position.x + Math.sin(t) * 1.5,
      camera.position.y + Math.cos(t * 0.7) * 1,
      camera.position.z - 3
    );

    // Milestone pulse
    milestoneObjects.forEach(node => {
      const wave = Math.sin(t * 2 + node.position.z * 0.3);
      node.scale.setScalar(1 + wave * 0.12);
      if (node.userData.halo) {
        node.userData.halo.material.opacity = 0.1 + Math.abs(wave) * 0.22;
        node.userData.halo.scale.setScalar(1 + Math.abs(wave) * 0.2);
      }
    });

    renderer.render(scene, camera);
  }

  animate();
}
