/**
 * about.js — About Page 3D Storytelling Experience
 * Scene: 4 timeline tracks (Career, Education, Leadership, Sports)
 *        rendered in 3D space. Scroll moves camera through each section.
 *        Interactive milestone hover reveals details.
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-about');
  if (!canvas) return;

  const THREE = await import('https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js');

  /* ── Renderer ── */
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);

  /* ── Scene / Camera ── */
  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 200);
  camera.position.set(0, 0, 18);

  /* ── Fog ── */
  scene.fog = new THREE.FogExp2(0x080c10, 0.04);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.2));
  const crimsonLight = new THREE.PointLight(0xc0392b, 3, 20);
  crimsonLight.position.set(0, 3, 10);
  scene.add(crimsonLight);
  const emeraldLight = new THREE.PointLight(0x6ee7b7, 2, 18);
  emeraldLight.position.set(0, -2, 5);
  scene.add(emeraldLight);

  /* ════════════════════════════════════════════
     TIMELINE DATA — 4 tracks on Y axis
  ════════════════════════════════════════════ */
  const TRACKS = [
    {
      label: 'Career',
      color: 0xc0392b,
      y: 3,
      milestones: [
        { year: '2024', title: 'AI Consultant', sub: 'UK & Bangladesh' },
        { year: '2022', title: 'SEO Lead', sub: 'Global Clients' },
        { year: '2020', title: 'Full Stack Dev', sub: 'Agency' },
        { year: '2018', title: 'Junior Dev', sub: 'Startup' },
      ],
    },
    {
      label: 'Education',
      color: 0x6ee7b7,
      y: 1,
      milestones: [
        { year: '2023', title: 'AI & ML', sub: 'Online Certifications' },
        { year: '2021', title: 'BSc Computing', sub: 'University' },
        { year: '2019', title: 'Web Dev Bootcamp', sub: 'Full Stack' },
        { year: '2017', title: 'A Levels', sub: 'Science & Maths' },
      ],
    },
    {
      label: 'Leadership',
      color: 0x7dd3fc,
      y: -1,
      milestones: [
        { year: '2024', title: 'Tech Lead', sub: '6-person team' },
        { year: '2022', title: 'Project Manager', sub: 'NGO Digital' },
        { year: '2020', title: 'Community Lead', sub: 'Dev Community' },
        { year: '2018', title: 'Mentor', sub: 'Junior Devs' },
      ],
    },
    {
      label: 'Sports',
      color: 0xa78bfa,
      y: -3,
      milestones: [
        { year: '2023', title: 'Cricket Captain', sub: 'Club League' },
        { year: '2021', title: 'Football Coach', sub: 'Youth Team' },
        { year: '2019', title: 'Marathon Runner', sub: '42km' },
        { year: '2017', title: 'Chess Champion', sub: 'Regional' },
      ],
    },
  ];

  /* ── Build timeline geometry ── */
  const milestoneObjects = [];

  TRACKS.forEach(track => {
    const trackColor = new THREE.Color(track.color);

    // Horizontal rail
    const railPts = [
      new THREE.Vector3(-9, track.y, 0),
      new THREE.Vector3(9, track.y, 0),
    ];
    const rail = new THREE.Line(
      new THREE.BufferGeometry().setFromPoints(railPts),
      new THREE.LineBasicMaterial({ color: track.color, transparent: true, opacity: 0.2 })
    );
    scene.add(rail);

    // Track label dot at left
    const labelDot = new THREE.Mesh(
      new THREE.SphereGeometry(0.12, 8, 8),
      new THREE.MeshBasicMaterial({ color: track.color })
    );
    labelDot.position.set(-10, track.y, 0);
    scene.add(labelDot);

    // Milestones
    track.milestones.forEach((m, mi) => {
      const x = -7 + mi * 4.5;

      // Node
      const node = new THREE.Mesh(
        new THREE.SphereGeometry(0.22, 12, 12),
        new THREE.MeshBasicMaterial({ color: track.color, transparent: true, opacity: 0.9 })
      );
      node.position.set(x, track.y, 0);
      node.userData = { track: track.label, milestone: m, origScale: 1, color: track.color, origOpacity: 0.9 };
      scene.add(node);
      milestoneObjects.push(node);

      // Vertical tick
      const tick = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([
          new THREE.Vector3(x, track.y - 0.3, 0),
          new THREE.Vector3(x, track.y + 0.3, 0),
        ]),
        new THREE.LineBasicMaterial({ color: track.color, transparent: true, opacity: 0.4 })
      );
      scene.add(tick);

      // Halo ring
      const halo = new THREE.Mesh(
        new THREE.RingGeometry(0.28, 0.36, 24),
        new THREE.MeshBasicMaterial({ color: track.color, side: THREE.DoubleSide, transparent: true, opacity: 0.0 })
      );
      halo.position.set(x, track.y, 0);
      halo.userData.isHalo = true;
      node.userData.halo = halo;
      scene.add(halo);
    });
  });

  /* ── Floating particles background ── */
  const bgGeo = new THREE.BufferGeometry();
  const bgPos = new Float32Array(1200 * 3);
  for (let i = 0; i < 1200; i++) {
    bgPos[i*3]   = (Math.random() - 0.5) * 40;
    bgPos[i*3+1] = (Math.random() - 0.5) * 16;
    bgPos[i*3+2] = (Math.random() - 0.5) * 10 - 5;
  }
  bgGeo.setAttribute('position', new THREE.BufferAttribute(bgPos, 3));
  const bgMat = new THREE.PointsMaterial({ color: 0x6ee7b7, size: 0.04, transparent: true, opacity: 0.3, blending: THREE.AdditiveBlending, depthWrite: false });
  scene.add(new THREE.Points(bgGeo, bgMat));

  /* ════════════════════════════════════════════
     SCROLL-DRIVEN CAMERA
  ════════════════════════════════════════════ */
  let scrollProg = 0;

  if (!prefersReduced && gsap && ScrollTrigger) {
    ScrollTrigger.create({
      trigger: document.body,
      start: 'top top',
      end: 'bottom bottom',
      onUpdate: s => { scrollProg = s.progress; }
    });
  } else {
    window.addEventListener('scroll', () => {
      const max = document.body.scrollHeight - window.innerHeight;
      scrollProg = max > 0 ? window.scrollY / max : 0;
    }, { passive: true });
  }

  /* ── Mouse ── */
  let mx = 0, my = 0;
  window.addEventListener('mousemove', e => {
    mx = (e.clientX / window.innerWidth  - 0.5) * 2;
    my = (e.clientY / window.innerHeight - 0.5) * 2;
  }, { passive: true });

  /* ── Raycaster for milestone hover ── */
  const raycaster = new THREE.Raycaster();
  const pointer   = new THREE.Vector2();
  let hoveredNode  = null;

  // Tooltip element
  const tooltip = document.createElement('div');
  tooltip.style.cssText = `
    position:fixed;top:0;left:0;pointer-events:none;z-index:9999;
    background:rgba(8,12,16,0.92);border:1px solid rgba(192,57,43,0.5);
    padding:10px 16px;border-radius:8px;color:#fff;font-size:13px;
    line-height:1.5;backdrop-filter:blur(8px);opacity:0;
    transition:opacity 0.2s;min-width:140px;
  `;
  document.body.appendChild(tooltip);

  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
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

  /* ── GSAP entrance ── */
  if (!prefersReduced && gsap) {
    milestoneObjects.forEach((node, i) => {
      gsap.from(node.scale, { x: 0, y: 0, z: 0, duration: 0.8, ease: 'back.out(2)', delay: i * 0.06 });
    });
  }

  /* ── Animation loop ── */
  if (prefersReduced) { renderer.render(scene, camera); return; }

  const clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();

    // Scroll camera: pan X through timeline, slight Y drift
    const targetX = -8 + scrollProg * 16;
    const targetY = scrollProg * 1.5 - 0.75;
    camera.position.x += (targetX + mx * 0.6 - camera.position.x) * 0.05;
    camera.position.y += (targetY - my * 0.3 - camera.position.y) * 0.05;
    camera.lookAt(camera.position.x, camera.position.y, 0);

    // Pulse milestone nodes
    milestoneObjects.forEach(node => {
      node.scale.setScalar(1 + Math.sin(t * 1.8 + node.position.x * 0.5) * 0.08);
      if (node.userData.halo) {
        const h = node.userData.halo;
        h.material.opacity = 0.12 + Math.abs(Math.sin(t + node.position.x)) * 0.14;
        h.scale.setScalar(1 + Math.sin(t * 2 + node.position.x) * 0.15);
      }
    });

    // Raycaster hover
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(milestoneObjects);

    if (hits.length > 0) {
      const hit = hits[0].object;
      if (hoveredNode !== hit) {
        if (hoveredNode) { hoveredNode.scale.setScalar(1); }
        hoveredNode = hit;
        const m = hit.userData.milestone;
        tooltip.innerHTML = `<strong style="color:#c0392b">${m.year}</strong><br>${m.title}<br><small style="color:#6ee7b7">${m.sub}</small>`;
        tooltip.style.opacity = '1';
      }
      hit.scale.setScalar(1.6);
      canvas.style.cursor = 'pointer';
    } else {
      if (hoveredNode) hoveredNode = null;
      tooltip.style.opacity = '0';
      canvas.style.cursor = '';
    }

    renderer.render(scene, camera);
  }

  animate();
}
