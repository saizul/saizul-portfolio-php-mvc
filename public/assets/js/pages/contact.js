/**
 * contact.js — Contact Globe
 * Interactive 3D globe showing Bangladesh, UK, USA.
 * Animated arc lines connect the three locations.
 * Click on a location dot reveals info card.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-contact');
  if (!canvas) return;

  const THREE = await import('https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js');

  /* ── Renderer ── */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);

  /* ── Scene / Camera ── */
  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 100);
  camera.position.set(0, 1.5, 8);
  camera.lookAt(0, 0, 0);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.25));
  const crimsonLight = new THREE.PointLight(0xc0392b, 2.5, 12);
  crimsonLight.position.set(-3, 3, 5);
  scene.add(crimsonLight);
  scene.add(Object.assign(new THREE.PointLight(0x6ee7b7, 1.8, 10), { position: new THREE.Vector3(3, -2, 4) }));

  /* ════════════════════════════════════════════
     GLOBE
  ════════════════════════════════════════════ */
  const GLOBE_R = 3;

  // Main sphere (dark ocean)
  const globeGeo = new THREE.SphereGeometry(GLOBE_R, 48, 48);
  const globeMat = new THREE.MeshBasicMaterial({ color: 0x0d1b2a, transparent: true, opacity: 0.85 });
  const globe    = new THREE.Mesh(globeGeo, globeMat);
  scene.add(globe);

  // Wireframe overlay
  const wireMat = new THREE.MeshBasicMaterial({ color: 0x1a2f35, wireframe: true, transparent: true, opacity: 0.18 });
  scene.add(new THREE.Mesh(new THREE.SphereGeometry(GLOBE_R * 1.002, 24, 24), wireMat));

  // Atmosphere glow
  const atmosGeo = new THREE.SphereGeometry(GLOBE_R * 1.06, 32, 32);
  const atmosMat = new THREE.MeshBasicMaterial({ color: 0x6ee7b7, transparent: true, opacity: 0.04, side: THREE.BackSide });
  scene.add(new THREE.Mesh(atmosGeo, atmosMat));

  /* ── Latitude/longitude rings ── */
  [-30, 0, 30].forEach(lat => {
    const r = Math.cos(lat * Math.PI / 180) * GLOBE_R;
    const h = Math.sin(lat * Math.PI / 180) * GLOBE_R;
    const ringGeo = new THREE.RingGeometry(r - 0.008, r + 0.008, 64);
    const ringMat = new THREE.MeshBasicMaterial({ color: 0x1a2f35, side: THREE.DoubleSide, transparent: true, opacity: 0.3 });
    const ring    = new THREE.Mesh(ringGeo, ringMat);
    ring.rotation.x = Math.PI / 2;
    ring.position.y = h;
    globe.add(ring);
  });

  /* ════════════════════════════════════════════
     LOCATIONS — lat/lng to 3D position
  ════════════════════════════════════════════ */
  function latLngTo3D(lat, lng, r) {
    const phi   = (90 - lat) * (Math.PI / 180);
    const theta = (lng + 180) * (Math.PI / 180);
    return new THREE.Vector3(
      -r * Math.sin(phi) * Math.cos(theta),
       r * Math.cos(phi),
       r * Math.sin(phi) * Math.sin(theta)
    );
  }

  const LOCATIONS = [
    { name: 'Bangladesh', city: 'Dhaka',  lat: 23.8103, lng: 90.4125,  color: 0xc0392b, info: 'Home Base — Available for remote & on-site projects' },
    { name: 'United Kingdom', city: 'London', lat: 51.5074, lng: -0.1278, color: 0x7dd3fc, info: 'UK Clients — SEO, AI & Web Development projects' },
    { name: 'United States',  city: 'New York', lat: 40.7128, lng: -74.0060, color: 0x6ee7b7, info: 'USA Clients — AI systems & consulting' },
  ];

  const locationDots = [];
  const globeGroup   = new THREE.Group();
  scene.add(globeGroup);
  globeGroup.add(globe);

  LOCATIONS.forEach(loc => {
    const pos3D = latLngTo3D(loc.lat, loc.lng, GLOBE_R);

    // Dot
    const dot = new THREE.Mesh(
      new THREE.SphereGeometry(0.1, 10, 10),
      new THREE.MeshBasicMaterial({ color: loc.color })
    );
    dot.position.copy(pos3D);
    dot.userData = { loc, origScale: 1 };
    globeGroup.add(dot);
    locationDots.push(dot);

    // Pulse ring (on globe surface)
    const pulse = new THREE.Mesh(
      new THREE.RingGeometry(0.12, 0.22, 20),
      new THREE.MeshBasicMaterial({ color: loc.color, side: THREE.DoubleSide, transparent: true, opacity: 0.3 })
    );
    pulse.position.copy(pos3D);
    pulse.lookAt(pos3D.clone().multiplyScalar(2)); // face outward
    dot.userData.pulse = pulse;
    globeGroup.add(pulse);
  });

  /* ── Arc lines between locations ── */
  function buildArc(posA, posB, color, segments = 60) {
    const pts = [];
    for (let i = 0; i <= segments; i++) {
      const t   = i / segments;
      const mid = posA.clone().lerp(posB, t);
      const r   = mid.length();
      mid.normalize().multiplyScalar(GLOBE_R + 0.5 + Math.sin(t * Math.PI) * 1.2);
      pts.push(mid);
    }
    const geo  = new THREE.BufferGeometry().setFromPoints(pts);
    const mat  = new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.45 });
    return new THREE.Line(geo, mat);
  }

  const arcLines = [];
  const pairs = [[0,1],[1,2],[0,2]];
  pairs.forEach(([a, b]) => {
    const posA = latLngTo3D(LOCATIONS[a].lat, LOCATIONS[a].lng, GLOBE_R);
    const posB = latLngTo3D(LOCATIONS[b].lat, LOCATIONS[b].lng, GLOBE_R);
    const arc  = buildArc(posA, posB, LOCATIONS[a].color);
    globeGroup.add(arc);
    arcLines.push(arc);
  });

  /* ── Animated dot travelling along arc ── */
  const travelDots = arcLines.map(arc => {
    const dot = new THREE.Mesh(
      new THREE.SphereGeometry(0.06, 6, 6),
      new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.9 })
    );
    dot.userData = { arc, t: Math.random(), speed: 0.18 + Math.random() * 0.12 };
    globeGroup.add(dot);
    return dot;
  });

  /* ── Background star field ── */
  const starGeo = new THREE.BufferGeometry();
  const starPos = new Float32Array(2000 * 3);
  for (let i = 0; i < 2000; i++) {
    starPos[i*3]   = (Math.random() - 0.5) * 60;
    starPos[i*3+1] = (Math.random() - 0.5) * 40;
    starPos[i*3+2] = (Math.random() - 0.5) * 15 - 10;
  }
  starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
  scene.add(new THREE.Points(starGeo, new THREE.PointsMaterial({ color: 0xffffff, size: 0.04, transparent: true, opacity: 0.25, blending: THREE.AdditiveBlending, depthWrite: false })));

  /* ── Info overlay ── */
  const overlay = document.createElement('div');
  overlay.style.cssText = `
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);
    z-index:10000;background:rgba(8,12,16,0.95);border:1px solid rgba(192,57,43,0.5);
    padding:28px;border-radius:14px;color:#fff;width:300px;max-width:90vw;
    backdrop-filter:blur(16px);opacity:0;pointer-events:none;transition:all 0.2s;
  `;
  overlay.innerHTML = `
    <button id="globe-close" style="position:absolute;top:10px;right:14px;background:none;border:none;color:#fff;font-size:18px;cursor:pointer;opacity:0.6">&times;</button>
    <div id="globe-content"></div>
  `;
  document.body.appendChild(overlay);
  overlay.querySelector('#globe-close').addEventListener('click', () => {
    overlay.style.opacity = '0'; overlay.style.pointerEvents = 'none';
  });

  /* ── Drag-to-rotate ── */
  let isDragging = false, lastX = 0, velX = 0;
  const rotGroup = globeGroup;
  canvas.addEventListener('pointerdown', e => { isDragging = true; lastX = e.clientX; velX = 0; });
  window.addEventListener('pointerup',   () => { isDragging = false; });
  window.addEventListener('pointermove', e => {
    if (!isDragging) return;
    velX = (e.clientX - lastX) * 0.007;
    rotGroup.rotation.y += velX;
    lastX = e.clientX;
  });

  /* ── Raycaster ── */
  const pointer   = new THREE.Vector2();
  const raycaster = new THREE.Raycaster();
  raycaster.params.Points = { threshold: 0.1 };

  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / window.innerWidth)  * 2 - 1;
    pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
  }, { passive: true });

  canvas.addEventListener('click', () => {
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(locationDots);
    if (!hits.length) return;
    const loc = hits[0].object.userData.loc;
    overlay.querySelector('#globe-content').innerHTML = `
      <div style="font-size:22px;margin-bottom:4px">${loc.name === 'Bangladesh' ? '🇧🇩' : loc.name === 'United Kingdom' ? '🇬🇧' : '🇺🇸'}</div>
      <h3 style="font-size:18px;font-weight:700;margin-bottom:4px">${loc.name}</h3>
      <div style="color:#6ee7b7;font-size:13px;margin-bottom:10px">${loc.city}</div>
      <p style="color:#94a3b8;font-size:13px;line-height:1.6">${loc.info}</p>
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

  /* ── GSAP entrance ── */
  if (!prefersReduced && gsap) {
    gsap.from(globeGroup.scale, { x: 0, y: 0, z: 0, duration: 1.5, ease: 'power3.out' });
    gsap.from(globeGroup.rotation, { y: -Math.PI * 0.5, duration: 2, ease: 'power2.out' });
  }

  if (prefersReduced) { renderer.render(scene, camera); return; }

  const clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();

    // Auto-rotate
    if (!isDragging) {
      velX *= 0.95;
      rotGroup.rotation.y += 0.003 + velX;
    }
    rotGroup.rotation.x = Math.sin(t * 0.2) * 0.04;

    // Location dot pulse
    locationDots.forEach((dot, i) => {
      const wave = Math.sin(t * 2 + i * 1.2);
      dot.scale.setScalar(1 + wave * 0.2);
      if (dot.userData.pulse) {
        dot.userData.pulse.scale.setScalar(1 + Math.abs(wave) * 0.5);
        dot.userData.pulse.material.opacity = 0.15 + Math.abs(wave) * 0.2;
      }
    });

    // Travel dots along arcs
    travelDots.forEach(td => {
      td.userData.t = (td.userData.t + td.userData.speed * 0.005) % 1;
      const pts = td.userData.arc.geometry.attributes.position;
      const idx  = Math.floor(td.userData.t * (pts.count - 1));
      td.position.set(pts.getX(idx), pts.getY(idx), pts.getZ(idx));
      td.material.opacity = Math.sin(td.userData.t * Math.PI) * 0.9;
    });

    // Arc shimmer
    arcLines.forEach((arc, i) => {
      arc.material.opacity = 0.25 + Math.abs(Math.sin(t * 1.2 + i * 0.8)) * 0.25;
    });

    // Light breathe
    crimsonLight.intensity = 2.2 + Math.sin(t * 1.5) * 0.5;

    renderer.render(scene, camera);
  }

  animate();
}
