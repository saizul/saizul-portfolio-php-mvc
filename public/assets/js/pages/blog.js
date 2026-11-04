/**
 * blog.js — Knowledge Galaxy
 * Blog posts rendered as floating 3D nodes grouped by category clusters.
 * Animated connections between related posts.
 * Click on a node navigates to the post (SEO-safe: content is server-rendered).
 */

export async function init({ gsap, ScrollTrigger, lenis }) {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('canvas-blog');
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
  camera.position.set(0, 0, 16);

  /* ── Lights ── */
  scene.add(new THREE.AmbientLight(0xffffff, 0.2));
  scene.add(Object.assign(new THREE.PointLight(0xc0392b, 3, 20), { position: new THREE.Vector3(-4, 3, 5) }));
  scene.add(Object.assign(new THREE.PointLight(0x6ee7b7, 2, 18), { position: new THREE.Vector3(4, -2, 5) }));

  /* ════════════════════════════════════════════
     PULL POST DATA from DOM (server-rendered)
  ════════════════════════════════════════════ */
  const postEls = Array.from(document.querySelectorAll('[data-post-id]'));
  const POSTS   = postEls.length > 0
    ? postEls.map(el => ({
        id:    el.dataset.postId,
        title: el.dataset.postTitle || 'Post',
        cat:   el.dataset.postCat   || 'general',
        url:   el.dataset.postUrl   || '#',
      }))
    : [
        { id:'1', title:'Building AI with LangChain',   cat:'ai',       url:'#' },
        { id:'2', title:'PHP MVC Deep Dive',            cat:'backend',  url:'#' },
        { id:'3', title:'Three.js Performance Guide',   cat:'frontend', url:'#' },
        { id:'4', title:'Technical SEO in 2024',        cat:'seo',      url:'#' },
        { id:'5', title:'OpenAI API Best Practices',    cat:'ai',       url:'#' },
        { id:'6', title:'MySQL Query Optimization',     cat:'backend',  url:'#' },
        { id:'7', title:'GSAP Animation Masterclass',   cat:'frontend', url:'#' },
        { id:'8', title:'Schema Markup Guide',          cat:'seo',      url:'#' },
        { id:'9', title:'RAG Architecture Patterns',    cat:'ai',       url:'#' },
      ];

  /* ── Category cluster centres ── */
  const CLUSTERS = {
    ai:       { center: new THREE.Vector3(-4,  2, 0), color: 0xc0392b },
    backend:  { center: new THREE.Vector3( 4,  2, 0), color: 0x6ee7b7 },
    frontend: { center: new THREE.Vector3(-4, -2, 0), color: 0x7dd3fc },
    seo:      { center: new THREE.Vector3( 4, -2, 0), color: 0xa78bfa },
    general:  { center: new THREE.Vector3( 0,  0, 0), color: 0xffffff },
  };

  // Cluster label halos
  Object.values(CLUSTERS).forEach(c => {
    const halo = new THREE.Mesh(
      new THREE.RingGeometry(0.8, 1.1, 32),
      new THREE.MeshBasicMaterial({ color: c.color, side: THREE.DoubleSide, transparent: true, opacity: 0.06 })
    );
    halo.position.copy(c.center);
    scene.add(halo);
    c.halo = halo;
  });

  /* ── Build post nodes ── */
  const postNodes = [];

  POSTS.forEach((post, i) => {
    const cluster = CLUSTERS[post.cat] || CLUSTERS.general;
    const color   = cluster.color;

    // Spread posts within cluster
    const angle  = (i * 2.39996) * 2; // golden angle spread
    const radius = 1.2 + (i % 3) * 0.7;
    const offset = new THREE.Vector3(
      Math.cos(angle) * radius * 0.8,
      Math.sin(angle) * radius * 0.5,
      (Math.random() - 0.5) * 1.5
    );
    const pos = cluster.center.clone().add(offset);

    const node = new THREE.Mesh(
      new THREE.IcosahedronGeometry(0.18, 1),
      new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.85 })
    );
    node.position.copy(pos);
    node.userData = { post, cluster, phase: Math.random() * Math.PI * 2, origPos: pos.clone() };
    scene.add(node);
    postNodes.push(node);

    // Connection line back to cluster centre
    const linePts = [cluster.center.clone(), pos.clone()];
    scene.add(new THREE.Line(
      new THREE.BufferGeometry().setFromPoints(linePts),
      new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.08 })
    ));
  });

  /* ── Intra-cluster connections between same-category nodes ── */
  for (let i = 0; i < postNodes.length; i++) {
    for (let j = i + 1; j < postNodes.length; j++) {
      if (postNodes[i].userData.post.cat !== postNodes[j].userData.post.cat) continue;
      if (Math.random() > 0.4) continue;
      scene.add(new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([postNodes[i].position.clone(), postNodes[j].position.clone()]),
        new THREE.LineBasicMaterial({ color: postNodes[i].userData.cluster.color, transparent: true, opacity: 0.05 })
      ));
    }
  }

  /* ── Background stars ── */
  const starGeo = new THREE.BufferGeometry();
  const starPos = new Float32Array(1200 * 3);
  for (let i = 0; i < 1200; i++) {
    starPos[i*3]   = (Math.random() - 0.5) * 50;
    starPos[i*3+1] = (Math.random() - 0.5) * 30;
    starPos[i*3+2] = (Math.random() - 0.5) * 10 - 8;
  }
  starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
  scene.add(new THREE.Points(starGeo, new THREE.PointsMaterial({ color: 0xffffff, size: 0.04, transparent: true, opacity: 0.2, blending: THREE.AdditiveBlending, depthWrite: false })));

  /* ── Tooltip ── */
  const tooltip = document.createElement('div');
  tooltip.style.cssText = `
    position:fixed;pointer-events:none;z-index:9999;
    background:rgba(8,12,16,0.93);border:1px solid rgba(192,57,43,0.4);
    padding:8px 14px;border-radius:8px;color:#fff;font-size:12px;line-height:1.5;
    backdrop-filter:blur(8px);opacity:0;transition:opacity 0.15s;max-width:200px;
  `;
  document.body.appendChild(tooltip);

  /* ── Mouse / raycaster ── */
  const pointer   = new THREE.Vector2();
  const raycaster = new THREE.Raycaster();
  let hoveredNode = null;
  let mx = 0, my = 0;

  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / window.innerWidth)  * 2 - 1;
    pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
    mx = pointer.x; my = -pointer.y;
    tooltip.style.left = (e.clientX + 14) + 'px';
    tooltip.style.top  = (e.clientY - 8)  + 'px';
  }, { passive: true });

  canvas.addEventListener('click', () => {
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(postNodes);
    if (hits.length > 0) {
      const url = hits[0].object.userData.post.url;
      if (url && url !== '#') window.location.href = url;
    }
  });

  /* ── Scroll ── */
  let scrollProg = 0;
  window.addEventListener('scroll', () => {
    scrollProg = window.scrollY / Math.max(1, document.body.scrollHeight - window.innerHeight);
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

    // Float nodes
    postNodes.forEach(node => {
      node.position.y = node.userData.origPos.y + Math.sin(t * 0.8 + node.userData.phase) * 0.12;
      node.rotation.y = t * 0.4 + node.userData.phase;
    });

    // Cluster halos pulse
    Object.values(CLUSTERS).forEach(c => {
      if (c.halo) c.halo.material.opacity = 0.03 + Math.abs(Math.sin(t * 1.2)) * 0.05;
    });

    // Camera parallax
    camera.position.x += (mx * 1.5 - camera.position.x) * 0.03;
    camera.position.y += (my * 0.8 - scrollProg * 1.5 - camera.position.y) * 0.03;
    camera.lookAt(0, 0, 0);

    // Raycaster
    raycaster.setFromCamera(pointer, camera);
    const hits = raycaster.intersectObjects(postNodes);

    if (hits.length > 0) {
      const hit = hits[0].object;
      if (hoveredNode !== hit) {
        if (hoveredNode) { hoveredNode.scale.setScalar(1); hoveredNode.material.opacity = 0.85; }
        hoveredNode = hit;
      }
      hit.scale.setScalar(1.7);
      hit.material.opacity = 1;
      tooltip.innerHTML = `<strong>${hit.userData.post.title}</strong><br><span style="color:#6ee7b7">${hit.userData.post.cat}</span>`;
      tooltip.style.opacity = '1';
      canvas.style.cursor = 'pointer';
    } else {
      if (hoveredNode) { hoveredNode.scale.setScalar(1); hoveredNode.material.opacity = 0.85; hoveredNode = null; }
      tooltip.style.opacity = '0';
      canvas.style.cursor = '';
    }

    renderer.render(scene, camera);
  }

  animate();
}
