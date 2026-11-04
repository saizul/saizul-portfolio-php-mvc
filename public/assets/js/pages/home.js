/**
 * home.js — Home Page 3D Experience
 * Scene: Particle Galaxy + AI Neural Network overlay + Floating Portrait
 *        Animated Logo + Scroll-driven camera + Interactive mouse parallax
 * Target: 60 FPS desktop, 45+ FPS mobile. Lazy-loaded via core.js.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-home');
  if (!canvas) return;

  /* ── Three.js dynamic import ── */
  const THREE = await import('https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js');

  /* ── Renderer ── */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.1;

  /* ── Scene / Camera ── */
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 200);
  camera.position.set(0, 0, 14);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.3));
  const crimsonLight = new THREE.PointLight(0xc0392b, 2.5, 30);
  crimsonLight.position.set(-4, 3, 6);
  scene.add(crimsonLight);
  const tealLight = new THREE.PointLight(0x6ee7b7, 1.8, 25);
  tealLight.position.set(4, -2, 8);
  scene.add(tealLight);

  /* ════════════════════════════════════════════
     1. PARTICLE GALAXY
  ════════════════════════════════════════════ */
  const isMobile = window.innerWidth < 768;
  const isLowEnd = (navigator.hardwareConcurrency ?? 8) <= 4;
  const PARTICLE_COUNT = isMobile ? 800 : (isLowEnd ? 1500 : 4000);
  const ARMS = 3;
  const ARM_SPREAD = 0.38;
  const galaxyGeo = new THREE.BufferGeometry();
  const positions = new Float32Array(PARTICLE_COUNT * 3);
  const colors    = new Float32Array(PARTICLE_COUNT * 3);
  const sizes     = new Float32Array(PARTICLE_COUNT);

  const palette = [
    new THREE.Color(0xffffff),
    new THREE.Color(0x6ee7b7),
    new THREE.Color(0xc0392b),
    new THREE.Color(0x7dd3fc),
    new THREE.Color(0xa78bfa),
  ];

  for (let i = 0; i < PARTICLE_COUNT; i++) {
    const arm   = i % ARMS;
    const t     = Math.random();
    const r     = Math.pow(t, 0.6) * 11;
    const theta = arm * (2 * Math.PI / ARMS) + r * 0.55 + (Math.random() - 0.5) * ARM_SPREAD;
    const spread = (Math.random() ** 3) * 1.2 * (Math.random() < 0.5 ? 1 : -1);

    positions[i*3]   = Math.cos(theta) * r + spread * 0.6;
    positions[i*3+1] = spread * 0.28;
    positions[i*3+2] = Math.sin(theta) * r + spread * 0.6;

    const col = palette[Math.floor(Math.random() * palette.length)];
    colors[i*3]   = col.r;
    colors[i*3+1] = col.g;
    colors[i*3+2] = col.b;

    sizes[i] = Math.random() * 2.2 + 0.4;
  }

  galaxyGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  galaxyGeo.setAttribute('color',    new THREE.BufferAttribute(colors, 3));
  galaxyGeo.setAttribute('size',     new THREE.BufferAttribute(sizes, 1));

  const galaxyMat = new THREE.PointsMaterial({
    size: 0.06,
    vertexColors: true,
    sizeAttenuation: true,
    transparent: true,
    opacity: 0.85,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
  });

  const galaxy = new THREE.Points(galaxyGeo, galaxyMat);
  scene.add(galaxy);

  /* ════════════════════════════════════════════
     2. LOGO GLOBE — wireframe IcosahedronGeometry
        with crimson vertex dots (brand echo)
  ════════════════════════════════════════════ */
  const globeGeo  = new THREE.IcosahedronGeometry(2.4, 3);
  const globeMat  = new THREE.MeshBasicMaterial({ color: 0xc0392b, wireframe: true, transparent: true, opacity: 0.07 });
  const globe     = new THREE.Mesh(globeGeo, globeMat);
  scene.add(globe);

  // Deduplicated vertex dots
  const rawPos = globeGeo.getAttribute('position');
  const seen   = new Set();
  const dotGroup = new THREE.Group();
  const dotGeo   = new THREE.SphereGeometry(0.038, 6, 6);
  const dotMat   = new THREE.MeshBasicMaterial({ color: 0xc0392b });

  for (let i = 0; i < rawPos.count; i++) {
    const x = parseFloat(rawPos.getX(i).toFixed(3));
    const y = parseFloat(rawPos.getY(i).toFixed(3));
    const z = parseFloat(rawPos.getZ(i).toFixed(3));
    const key = `${x},${y},${z}`;
    if (seen.has(key)) continue;
    seen.add(key);
    const dot = new THREE.Mesh(dotGeo, dotMat);
    dot.position.set(x, y, z);
    dotGroup.add(dot);
  }
  scene.add(dotGroup);

  /* ════════════════════════════════════════════
     3. FLOATING PORTRAIT
  ════════════════════════════════════════════ */
  const portraitGroup = new THREE.Group();
  scene.add(portraitGroup);

  const texLoader  = new THREE.TextureLoader();
  const portraitTex = await new Promise(res => {
    texLoader.load('/assets/images/saizul-portrait.jpg', res, undefined, () => res(null));
  });

  if (portraitTex) {
    const pGeo = new THREE.PlaneGeometry(2.4, 3.2);
    const pMat = new THREE.MeshBasicMaterial({ map: portraitTex, transparent: true, side: THREE.FrontSide });
    const portrait = new THREE.Mesh(pGeo, pMat);
    portraitGroup.add(portrait);

    // Crimson frame
    const edgesGeo = new THREE.EdgesGeometry(pGeo);
    const edgesMat = new THREE.LineBasicMaterial({ color: 0xc0392b, transparent: true, opacity: 0.7 });
    portraitGroup.add(new THREE.LineSegments(edgesGeo, edgesMat));
  }

  portraitGroup.position.set(3.5, 0, 0);

  /* ════════════════════════════════════════════
     4. AI NEURAL NETWORK OVERLAY
        (background layer, behind portrait)
  ════════════════════════════════════════════ */
  const NN_LAYERS  = [4, 6, 6, 4];
  const NN_LAYER_X = [-5.5, -2, 1, 4];
  const nnGroup    = new THREE.Group();
  const nnNodes    = [];
  const nnEdges    = [];

  const nodeMat = new THREE.MeshBasicMaterial({ color: 0x6ee7b7, transparent: true, opacity: 0.55 });
  const nodeGeo = new THREE.SphereGeometry(0.09, 8, 8);
  const edgeMat = new THREE.LineBasicMaterial({ color: 0x6ee7b7, transparent: true, opacity: 0.07 });

  NN_LAYERS.forEach((count, li) => {
    const layerNodes = [];
    for (let ni = 0; ni < count; ni++) {
      const y = (ni - (count - 1) / 2) * 1.1;
      const mesh = new THREE.Mesh(nodeGeo, nodeMat.clone());
      mesh.position.set(NN_LAYER_X[li], y - 1.5, -4);
      mesh.userData = { baseY: y - 1.5, phase: Math.random() * Math.PI * 2, layerIdx: li };
      nnGroup.add(mesh);
      layerNodes.push(mesh);
      nnNodes.push(mesh);
    }

    if (li > 0) {
      const prevLayer = nnNodes.slice(nnNodes.length - count - NN_LAYERS[li-1], nnNodes.length - count);
      layerNodes.forEach(n => {
        prevLayer.forEach(p => {
          const pts = [p.position.clone(), n.position.clone()];
          const line = new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), edgeMat.clone());
          nnGroup.add(line);
          nnEdges.push(line);
        });
      });
    }
  });

  scene.add(nnGroup);

  /* ════════════════════════════════════════════
     5. SCROLL-DRIVEN CAMERA
  ════════════════════════════════════════════ */
  let scrollProgress = 0;
  let targetZ = 14;

  if (!prefersReduced && gsap && ScrollTrigger) {
    ScrollTrigger.create({
      trigger: document.body,
      start: 'top top',
      end: 'bottom bottom',
      onUpdate: self => { scrollProgress = self.progress; }
    });
  } else {
    window.addEventListener('scroll', () => {
      const max = document.body.scrollHeight - window.innerHeight;
      scrollProgress = max > 0 ? window.scrollY / max : 0;
    }, { passive: true });
  }

  /* ════════════════════════════════════════════
     6. MOUSE PARALLAX
  ════════════════════════════════════════════ */
  let mx = 0, my = 0;
  window.addEventListener('mousemove', e => {
    mx = (e.clientX / window.innerWidth  - 0.5) * 2;
    my = (e.clientY / window.innerHeight - 0.5) * 2;
  }, { passive: true });

  /* ════════════════════════════════════════════
     7. RESIZE
  ════════════════════════════════════════════ */
  const onResize = () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  };
  window.addEventListener('resize', onResize);

  /* ════════════════════════════════════════════
     8. GSAP HERO ENTRANCE
  ════════════════════════════════════════════ */
  if (!prefersReduced && gsap) {
    gsap.from(globe.material, { opacity: 0, duration: 2, ease: 'power2.out' });
    gsap.from(portraitGroup.position, { x: 6, duration: 1.6, ease: 'power3.out', delay: 0.4 });
    gsap.from(portraitGroup.children.map(c => c.material), {
      opacity: 0, duration: 1.4, ease: 'power2.out', delay: 0.6, stagger: 0.1
    });
    gsap.from(galaxyMat, { opacity: 0, duration: 2.5, ease: 'power2.out', delay: 0.2 });
  }

  /* ════════════════════════════════════════════
     9. ANIMATION LOOP
  ════════════════════════════════════════════ */
  if (prefersReduced) {
    renderer.render(scene, camera);
    return;
  }

  let frame = 0;
  const clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();
    frame++;

    // Galaxy rotation
    galaxy.rotation.y = t * 0.04;
    galaxy.rotation.x = Math.sin(t * 0.012) * 0.06;

    // Globe + dots rotation
    globe.rotation.y    = t * 0.08;
    globe.rotation.x    = t * 0.03;
    dotGroup.rotation.y = globe.rotation.y;
    dotGroup.rotation.x = globe.rotation.x;

    // Portrait float
    portraitGroup.position.y = Math.sin(t * 0.6) * 0.12;
    portraitGroup.rotation.y = Math.sin(t * 0.3) * 0.04 + mx * 0.03;
    portraitGroup.rotation.x = -my * 0.025;

    // Neural network pulse (every 2nd frame for perf)
    if (frame % 2 === 0) {
      nnNodes.forEach(n => {
        const wave = Math.sin(t * 1.6 - n.userData.layerIdx * 0.9 + n.userData.phase);
        n.material.opacity = 0.3 + Math.max(0, wave) * 0.5;
        n.scale.setScalar(0.9 + Math.max(0, wave) * 0.4);
      });
      nnEdges.forEach((e, i) => {
        e.material.opacity = 0.04 + Math.abs(Math.sin(t * 1.2 + i * 0.3)) * 0.12;
      });
    }

    // Scroll camera
    targetZ = 14 + scrollProgress * 8;
    camera.position.z += (targetZ - camera.position.z) * 0.06;
    camera.position.y += (-scrollProgress * 2 - camera.position.y) * 0.04;

    // Mouse parallax on camera
    camera.position.x += (mx * 0.5 - camera.position.x) * 0.04;

    // Crimson light pulse
    crimsonLight.intensity = 2.0 + Math.sin(t * 1.8) * 0.6;

    renderer.render(scene, camera);
  }

  animate();

  /* ── Cleanup ── */
  return () => {
    window.removeEventListener('resize', onResize);
    renderer.dispose();
    galaxyGeo.dispose();
    galaxyMat.dispose();
    globeGeo.dispose();
    globeMat.dispose();
  };
}
