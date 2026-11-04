/**
 * skills.js — Skills Universe
 * Skills are planets orbiting in a solar-system layout.
 * Each category has its own orbital ring.
 * Hover reveals: experience level, related projects, tools.
 * Drag to rotate the whole system.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-skills');
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
  camera.position.set(0, 3, 14);
  camera.lookAt(0, 0, 0);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.3));
  const sunLight = new THREE.PointLight(0xc0392b, 4, 20);
  scene.add(sunLight);
  scene.add(new THREE.PointLight(0x6ee7b7, 1.5, 30));

  /* ── Sun (central node) ── */
  const sunGeo  = new THREE.IcosahedronGeometry(0.55, 2);
  const sunMat  = new THREE.MeshBasicMaterial({ color: 0xc0392b, wireframe: false });
  const sun     = new THREE.Mesh(sunGeo, sunMat);
  scene.add(sun);

  // Sun glow ring
  const glowRing = new THREE.Mesh(
    new THREE.RingGeometry(0.6, 1.0, 32),
    new THREE.MeshBasicMaterial({ color: 0xc0392b, side: THREE.DoubleSide, transparent: true, opacity: 0.12 })
  );
  glowRing.rotation.x = -Math.PI / 2;
  scene.add(glowRing);

  /* ════════════════════════════════════════════
     SKILL DATA — pull from DOM or fallback
  ════════════════════════════════════════════ */
  const skillEls = Array.from(document.querySelectorAll('[data-skill-id]'));

  const ORBITS = [
    {
      label: 'AI & ML',
      radius: 2.6,
      color: 0xc0392b,
      tilt: 0.18,
      speed: 0.22,
      skills: skillEls.filter(e => e.dataset.skillCat === 'ai').length > 0
        ? skillEls.filter(e => e.dataset.skillCat === 'ai').map(e => ({ name: e.dataset.skillName, pct: e.dataset.skillPct || 90 }))
        : [{ name: 'TensorFlow', pct: 88 }, { name: 'PyTorch', pct: 82 }, { name: 'LangChain', pct: 90 }, { name: 'OpenAI', pct: 95 }],
    },
    {
      label: 'Backend',
      radius: 4.2,
      color: 0x6ee7b7,
      tilt: -0.12,
      speed: 0.15,
      skills: [{ name: 'PHP', pct: 92 }, { name: 'Python', pct: 88 }, { name: 'MySQL', pct: 85 }, { name: 'Node.js', pct: 78 }, { name: 'Laravel', pct: 80 }],
    },
    {
      label: 'Frontend',
      radius: 5.8,
      color: 0x7dd3fc,
      tilt: 0.25,
      speed: 0.10,
      skills: [{ name: 'React', pct: 84 }, { name: 'Three.js', pct: 88 }, { name: 'GSAP', pct: 85 }, { name: 'Tailwind', pct: 92 }, { name: 'Next.js', pct: 80 }],
    },
    {
      label: 'SEO & Marketing',
      radius: 7.4,
      color: 0xa78bfa,
      tilt: -0.20,
      speed: 0.07,
      skills: [{ name: 'Technical SEO', pct: 95 }, { name: 'Analytics', pct: 88 }, { name: 'Schema', pct: 90 }, { name: 'Content Strategy', pct: 85 }],
    },
  ];

  const planetObjects = [];

  ORBITS.forEach(orbit => {
    // Orbit ring
    const ringGeo = new THREE.RingGeometry(orbit.radius - 0.02, orbit.radius + 0.02, 96);
    const ringMat = new THREE.MeshBasicMaterial({ color: orbit.color, side: THREE.DoubleSide, transparent: true, opacity: 0.15 });
    const ring    = new THREE.Mesh(ringGeo, ringMat);
    ring.rotation.x = -Math.PI / 2 + orbit.tilt;
    scene.add(ring);

    // Planets
    const pCount = orbit.skills.length;
    orbit.skills.forEach((skill, i) => {
      const angle = (i / pCount) * Math.PI * 2;
      const size  = 0.12 + (parseInt(skill.pct) / 100) * 0.16;

      const planet = new THREE.Mesh(
        new THREE.IcosahedronGeometry(size, 1),
        new THREE.MeshBasicMaterial({ color: orbit.color, transparent: true, opacity: 0.85 })
      );
      planet.userData = {
        orbit,
        skill,
        baseAngle: angle,
        speed: orbit.speed,
        radius: orbit.radius,
        tilt: orbit.tilt,
      };

      // Halo
      const halo = new THREE.Mesh(
        new THREE.RingGeometry(size * 1.4, size * 2.0, 20),
        new THREE.MeshBasicMaterial({ color: orbit.color, side: THREE.DoubleSide, transparent: true, opacity: 0.0 })
      );
      planet.userData.halo = halo;
      scene.add(halo);
      scene.add(planet);
      planetObjects.push(planet);
    });
  });

  /* ── Star field background ── */
  const starGeo = new THREE.BufferGeometry();
  const starPos = new Float32Array(1800 * 3);
  for (let i = 0; i < 1800; i++) {
    starPos[i*3]   = (Math.random() - 0.5) * 60;
    starPos[i*3+1] = (Math.random() - 0.5) * 40;
    starPos[i*3+2] = (Math.random() - 0.5) * 20 - 10;
  }
  starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
  scene.add(new THREE.Points(starGeo, new THREE.PointsMaterial({ color: 0xffffff, size: 0.04, transparent: true, opacity: 0.3, blending: THREE.AdditiveBlending, depthWrite: false })));

  /* ── Tooltip ── */
  const tooltip = document.createElement('div');
  tooltip.style.cssText = `
    position:fixed;top:0;left:0;pointer-events:none;z-index:9999;
    background:rgba(8,12,16,0.93);border:1px solid rgba(110,231,183,0.4);
    padding:12px 16px;border-radius:10px;color:#fff;font-size:13px;line-height:1.6;
    backdrop-filter:blur(10px);opacity:0;transition:opacity 0.2s;min-width:160px;
  `;
  document.body.appendChild(tooltip);

  /* ── Drag-to-rotate ── */
  let isDragging = false, dragStartX = 0, dragStartY = 0;
  let rotX = 0, rotY = 0, velX = 0, velY = 0;
  let lastX = 0, lastY = 0;
  const pivotGroup = new THREE.Group();
  scene.add(pivotGroup);

  canvas.addEventListener('pointerdown', e => { isDragging = true; dragStartX = e.clientX; dragStartY = e.clientY; lastX = e.clientX; lastY = e.clientY; });
  window.addEventListener('pointerup',   () => { isDragging = false; });
  window.addEventListener('pointermove', e => {
    if (isDragging) {
      velX = (e.clientX - lastX) * 0.006;
      velY = (e.clientY - lastY) * 0.004;
      rotY += velX;
      rotX += velY;
      lastX = e.clientX; lastY = e.clientY;
    }
  });

  /* ── Raycaster ── */
  const raycaster = new THREE.Raycaster();
  const pointer   = new THREE.Vector2();
  let hoveredPlanet = null;

  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / window.innerWidth)  * 2 - 1;
    pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
    tooltip.style.left = (e.clientX + 16) + 'px';
    tooltip.style.top  = (e.clientY - 10) + 'px';
  }, { passive: true });

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

    // Inertia
    if (!isDragging) { velX *= 0.94; velY *= 0.94; rotY += velX; rotX += velY; }
    rotX = Math.max(-0.5, Math.min(0.5, rotX));

    // Sun pulse
    sun.scale.setScalar(1 + Math.sin(t * 2) * 0.05);
    sunLight.intensity = 3.5 + Math.sin(t * 1.5) * 0.8;
    glowRing.material.opacity = 0.08 + Math.sin(t * 2) * 0.06;
    glowRing.scale.setScalar(1 + Math.sin(t * 2) * 0.04);

    // Orbit planets
    planetObjects.forEach(p => {
      const { baseAngle, speed, radius, tilt } = p.userData;
      const angle = baseAngle + t * speed + rotY;
      p.position.x = Math.cos(angle) * radius;
      p.position.y = Math.sin(angle * 0.5) * Math.sin(tilt) * radius * 0.6;
      p.position.z = Math.sin(angle) * radius;
      p.rotation.y = t * 0.5;

      if (p.userData.halo) {
        p.userData.halo.position.copy(p.position);
        p.userData.halo.lookAt(camera.position);
      }
    });

    // Raycaster hover
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(planetObjects);

    if (hits.length > 0) {
      const hit = hits[0].object;
      if (hoveredPlanet !== hit) {
        if (hoveredPlanet) {
          hoveredPlanet.scale.setScalar(1);
          if (hoveredPlanet.userData.halo) hoveredPlanet.userData.halo.material.opacity = 0;
        }
        hoveredPlanet = hit;
      }
      hit.scale.setScalar(1.7);
      if (hit.userData.halo) hit.userData.halo.material.opacity = 0.3;

      const s = hit.userData.skill;
      tooltip.innerHTML = `
        <strong>${s.name}</strong><br>
        <span style="color:#6ee7b7">${hit.userData.orbit.label}</span><br>
        <div style="margin-top:6px;background:#1a2f35;border-radius:4px;height:4px;width:120px">
          <div style="background:#c0392b;height:100%;border-radius:4px;width:${s.pct}%"></div>
        </div>
        <small style="color:#94a3b8">${s.pct}% proficiency</small>
      `;
      tooltip.style.opacity = '1';
      canvas.style.cursor = 'pointer';
    } else {
      if (hoveredPlanet) {
        hoveredPlanet.scale.setScalar(1);
        if (hoveredPlanet.userData.halo) hoveredPlanet.userData.halo.material.opacity = 0;
        hoveredPlanet = null;
      }
      tooltip.style.opacity = '0';
      canvas.style.cursor = '';
    }

    renderer.render(scene, camera);
  }

  animate();
}
