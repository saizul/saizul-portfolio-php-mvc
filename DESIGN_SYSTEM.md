# DESIGN_SYSTEM.md — Saizul Amin Personal Brand Platform

> **Document Type:** Visual Identity & UI Design Language  
> **Status:** Approved Reference  
> **Last Updated:** 2026-06-01

---

## 1. Brand Analysis

### Portrait Study

Saizul Amin's professional portraits establish a clear visual language:

- **Subject presence:** Direct, confident eye contact. Warm, approachable smile. Projects authority without coldness.
- **Wardrobe:** Dark navy blazer with white or light blue dress shirt — structured, professional, modern.
- **Studio lighting:** Soft, directional studio lighting with controlled shadows. No harsh contrast.
- **Backgrounds:** Two dominant tones — a muted cool grey and a deep teal/charcoal. Both cinematic in quality.
- **Mood:** The portraits land between _editorial_ and _corporate_ — not a startup hoodie, not a stiff passport photo. Premium.

### Logo Study

The logo is a **globe wireframe** — latitude and longitude lines rendered in dark ink, with **red node dots** at key intersections. A large **serif "S"** in crimson anchors the composition.

- The globe = global reach, interconnected systems, the AI/data professional
- The wireframe aesthetic = technical precision, Three.js visual language, structural thinking
- The serif "S" = classical confidence, not a startup monogram
- Crimson red = the sole accent — bold, singular, high-stakes

---

## 2. Brand Colors

### Primary Palette

| Token | Hex | Usage |
|---|---|---|
| `--color-void` | `#080c10` | Page background, deepest shadow |
| `--color-deep-navy` | `#0d1b2a` | Card backgrounds, panel fills |
| `--color-teal-dark` | `#1a2f35` | Section backgrounds, subtle differentiation |
| `--color-teal-mid` | `#264d54` | Hover states, gradient midpoints |
| `--color-border` | `#1e3040` | Dividers, card borders |

### Accent Palette

| Token | Hex | Usage |
|---|---|---|
| `--color-crimson` | `#c0392b` | Primary brand accent — logo, CTAs, highlights |
| `--color-crimson-light` | `#e74c3c` | Hover state of crimson |
| `--color-crimson-glow` | `rgba(192,57,43,0.15)` | Glow effects, card hover backgrounds |
| `--color-emerald` | `#6ee7b7` | Secondary accent — data points, skill indicators, Three.js particles |
| `--color-blue-soft` | `#60a5fa` | Tertiary accent — links, code, tags |

### Neutral / Text Palette

| Token | Hex | Usage |
|---|---|---|
| `--color-white` | `#ffffff` | Primary text, headings |
| `--color-grey-100` | `#f0f4f8` | High-contrast subheadings |
| `--color-grey-300` | `#94a3b8` | Body text, secondary labels |
| `--color-grey-500` | `#475569` | Disabled, placeholder |
| `--color-grey-700` | `#1e293b` | Card edge highlights |

### Color Psychology & Application Rules

1. **Crimson is earned** — use it sparingly. One primary CTA per section maximum. Never as a background fill.
2. **Teal is the atmosphere** — backgrounds and section breaks use the teal family to create depth without noise.
3. **Emerald is data** — Three.js particles, skill bars, live indicators. Never in static copy.
4. **White is signal** — all primary headings and key numbers are pure white. No off-white softening.
5. **Navy is structure** — cards, panels, sidebar backgrounds live in navy. Keeps the UI grounded.

---

## 3. Typography

### Typeface Stack

| Role | Typeface | Weight | Notes |
|---|---|---|---|
| **Display** | Inter | 800–900 | Tight tracking (`-0.04em`), uppercase optional for labels |
| **Heading** | Inter | 600–700 | Default heading style |
| **Body** | Inter | 400 | Line-height 1.7, `--color-grey-300` |
| **Mono / Code** | JetBrains Mono | 400–500 | Tech stack tags, code blocks |
| **Eyebrow** | Inter | 600 | ALL CAPS, `0.12em` letter-spacing, crimson or emerald |

### Type Scale

| Token | Size | Usage |
|---|---|---|
| `--text-display-1` | `clamp(3.5rem, 8vw, 7.5rem)` | Hero name, landmark statements |
| `--text-display-2` | `clamp(2.5rem, 5vw, 5rem)` | Section hero titles |
| `--text-heading-1` | `clamp(1.75rem, 3vw, 2.75rem)` | H1 within sections |
| `--text-heading-2` | `clamp(1.25rem, 2vw, 1.75rem)` | H2, card titles |
| `--text-body-lg` | `1.125rem` | Lead paragraphs |
| `--text-body` | `1rem` | Standard copy |
| `--text-sm` | `0.875rem` | Captions, metadata, tags |
| `--text-xs` | `0.75rem` | Labels, badges |

### Typography Rules

- **No system fonts** in hero or section headlines. Always Inter.
- **Gradient text** used _only_ on the single most important word per section — typically the name or a keyword noun.
- **All-caps eyebrow** labels precede every section title: e.g., `ABOUT ME` / `AI PROJECTS` / `OPEN TO WORK`.
- **Numbers as art** — statistics (years of experience, projects shipped) use `--text-display-1` weight with a small unit suffix in `--color-grey-300`.

---

## 4. UI Design Language

### Aesthetic Direction

**Premium Dark Editorial + Technical Precision**

The site fuses three visual influences:
1. **Apple.com** — whitespace mastery, single-focal-point layouts, type as imagery
2. **Linear.app** — dark UI done right: no muddy blacks, precise grid, purposeful motion
3. **Stripe.com** — trust-building through clean structure, subtle gradients, zero noise

The result: a site that feels like a _product_, not a portfolio.

### Surface Language

| Surface | Treatment |
|---|---|
| Page background | `--color-void` (`#080c10`) |
| Section alt background | `--color-teal-dark` (`#1a2f35`) with soft radial gradient overlay |
| Cards | `--color-deep-navy` border `--color-border`, `border-radius: 16px`, `box-shadow: 0 4px 32px rgba(0,0,0,0.4)` |
| Cards on hover | Crimson glow `box-shadow: 0 8px 48px rgba(192,57,43,0.12)`, `translateY(-4px)` |
| Modals / Drawers | `backdrop-filter: blur(24px)`, semi-transparent `rgba(13,27,42,0.9)` |
| Dividers | `1px solid --color-border`, opacity 0.5 |

### Glassmorphism Rule
Glass effects (blur + transparency) are reserved for the navigation bar and modal overlays only. Cards are solid-background panels — glass on cards creates visual mud.

### Spacing System

| Token | Value | Usage |
|---|---|---|
| `--space-section` | `clamp(80px, 12vw, 160px)` | Vertical padding per section |
| `--space-container` | `1280px max-width, 24px side padding` | Content container |
| `--space-card-pad` | `32px` | Card inner padding |
| `--space-gap` | `24px` | Default grid gap |
| `--space-gap-lg` | `40px` | Wide grid gap |

### Interaction States

| State | Treatment |
|---|---|
| Hover (links) | Crimson underline slides in from left, 200ms ease |
| Hover (cards) | `translateY(-4px)`, crimson glow shadow |
| Hover (buttons) | Background shifts 10% lighter, no scale |
| Focus | Crimson `outline: 2px solid var(--color-crimson)`, `outline-offset: 4px` |
| Active | `scale(0.97)` for 80ms |
| Disabled | `opacity: 0.4`, `cursor: not-allowed` |

### Border Radius

| Element | Radius |
|---|---|
| Cards | `16px` |
| Buttons | `8px` |
| Tags / Badges | `9999px` (pill) |
| Inputs | `8px` |
| Image frames | `12px` |
| Modals | `20px` |

### Elevation / Depth

Rather than pure box-shadow stacks, depth is created by:
- **Z-layered backgrounds** — void → teal-dark → navy panels → white text
- **Particle systems** in Three.js canvas occupying the deepest z-layer
- **Content floating above** via CSS `position: relative; z-index: 10`
- Shadow scale: `none` → `0 2px 8px rgba(0,0,0,0.3)` → `0 8px 32px rgba(0,0,0,0.5)` → crimson glow

---

## 5. Hero Section Concept

### Layout

**Full-viewport (100svh) split layout:**

```
┌─────────────────────────────────────────────────────┐
│  NAV (glassmorphism, fixed top)                      │
├─────────────────┬───────────────────────────────────┤
│                 │                                    │
│  TEXT COLUMN    │    PORTRAIT / 3D CANVAS            │
│  (left, 55%)    │    (right, 45%)                    │
│                 │                                    │
│  [eyebrow]      │    ┌─────────────────────┐        │
│  SAIZUL AMIN    │    │  Portrait photo      │        │
│  [gradient]     │    │  with particle       │        │
│                 │    │  overlay from        │        │
│  [tagline]      │    │  Three.js canvas     │        │
│  [2 CTAs]       │    └─────────────────────┘        │
│  [stats row]    │                                    │
├─────────────────┴───────────────────────────────────┤
│  SCROLL INDICATOR (animated chevron + "Scroll")     │
└─────────────────────────────────────────────────────┘
```

### Visual Treatment — Portrait

- Portrait rendered as a `<img>` with `object-fit: cover`, placed inside a styled frame
- Frame: subtle crimson gradient border on right/bottom edge (`border-image` or `::after` pseudo)
- Three.js canvas (`#canvas-home`) positioned `absolute` behind and around the portrait
- Particle system drifts over and behind the image, creating depth
- Portrait has a slight right-facing composition — negative space on left filled by text

### Headline Treatment

```
[EYEBROW: ALL CAPS EMERALD]
AI Engineer & Full-Stack Developer

SAIZUL [gradient: white→crimson] AMIN
[tagline: Building intelligent systems
that scale from idea to production.]
```

- Name occupies full width of the text column at `--text-display-1`
- "AMIN" receives gradient text treatment (white→crimson, left→right)
- Tagline at `--text-body-lg` in `--color-grey-300`

### Stats Row (below CTAs)

Three stat chips, separated by vertical dividers:

```
5+ Years   |   20+ Projects   |   3 AI Products
```

Numbers in `--text-heading-1` white, labels in `--text-sm` grey-300.

### Entry Animation (GSAP)

1. **t=0** — Page black. Lenis begins.
2. **t=0.2s** — Eyebrow fades + slides up (GSAP `from: {opacity:0, y:20}`)
3. **t=0.4s** — Name stagger-reveals word by word
4. **t=0.6s** — Tagline and CTAs fade in
5. **t=0.8s** — Stats row slides up
6. **t=0.5s** (parallel) — Portrait frame fades in with subtle scale `from: {scale:0.95}`
7. **t=1s** (parallel) — Three.js particles begin spawning from center outward

---

## 6. 3D Scene Concept

### Design Philosophy

Every Three.js scene **echoes the logo** — the globe wireframe is the visual DNA. Each page takes one element of the logo and reinterprets it:

| Page | Visual Echo | Scene Concept |
|---|---|---|
| Home | Globe wireframe + particles | Particle field + IcosahedronGeometry wireframe, camera drift |
| About | Orbital rings | TorusGeometry rings with animated sphere core + dot orbiters |
| AI Projects | Node network | Neural net: SphereGeometry nodes + Line connections, pulse animation |
| Experience | Floating planes | PlaneGeometry cards with EdgesGeometry outlines, sine-wave bobbing |
| Skills | Constellation | Fibonacci sphere node distribution, constellation lines, opacity pulse |
| Portfolio | Card carousel | CARD_COUNT=6 circle carousel rotating around a central axis |
| Contact | Fluid wave | PlaneGeometry(14,10,80,80) wave mesh, vertex Y animation |

### Shared Scene Rules

1. **Background:** Always `alpha: true`, `clearColor(0x000000, 0)` — the page background bleeds through
2. **Primary particle color:** `0xffffff` at opacity 0.15–0.25 for ambient haze
3. **Accent particle color:** `0x6ee7b7` (emerald) for primary interactive elements
4. **Crimson node dots:** `0xc0392b` used on the globe/logo motif only — consistent with the logo's red nodes
5. **Pixel ratio cap:** `Math.min(window.devicePixelRatio, 2)` always
6. **Reduced motion:** Early return if `prefers-reduced-motion: reduce`
7. **Cleanup:** `cancelAnimationFrame` + `renderer.dispose()` on `pagehide`

### Home Page Globe — Detailed Spec

The home hero Three.js scene should directly reference the logo:

- `IcosahedronGeometry(2.5, 4)` as the globe wireframe — rendered `wireframe: true`, white at opacity 0.06
- 12–16 `SphereGeometry(0.04)` nodes placed at icosahedron vertices — rendered in crimson `0xc0392b` at opacity 0.7
- 2500 ambient particles in `BufferGeometry` — white, size 0.008, opacity 0.2
- Globe rotates slowly on Y axis: `t += 0.003`
- Camera drifts with mouse parallax: smooth lerp to `mouseX * 0.3`

---

## 7. Portfolio Design Concept

### Layout: Full-Bleed Case Studies

Each project is a **full-width horizontal card** alternating image-left / text-right:

```
┌──────────────────────────────────────────────────────────┐
│  ┌───────────────────┐   ┌──────────────────────────┐   │
│  │                   │   │  [TAG: AI PRODUCT]        │   │
│  │  Project          │   │                           │   │
│  │  Screenshot /     │   │  Project Title            │   │
│  │  Mockup           │   │  (heading-1)              │   │
│  │                   │   │                           │   │
│  │  [hover: crimson  │   │  Short description        │   │
│  │   overlay + link] │   │  2–3 sentences            │   │
│  │                   │   │                           │   │
│  └───────────────────┘   │  [TECH TAGS]              │   │
│                           │                           │   │
│                           │  [View Project →]  [GitHub]  │
│                           └──────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
```

### Alternating Rhythm

- Odd cards: image left, text right
- Even cards: text left, image right
- The reversal creates visual breathing room and stops the eye from skipping

### Project Card Details

- **Image frame:** `border-radius: 12px`, `overflow: hidden`, subtle crimson border-bottom `4px` as accent
- **Hover on image:** Dark overlay `rgba(192,57,43,0.15)` + "View Project" text fades in
- **Tech tags:** Pill badges in `--color-deep-navy` with `--color-border` border, mono font
- **CTA buttons:** "View Project" in `--btn-primary` (crimson), "GitHub" in `--btn-outline`

### Filter System

Tabs above the grid: `All` / `AI & ML` / `Full-Stack` / `Open Source`
- Active tab: crimson underline + white text
- Inactive: grey-300 text, no border
- Filter is JS-driven (no page reload): `data-type` attribute matching

### Micro-interaction

On page load, project cards stagger-reveal with GSAP:
```
from: { opacity: 0, y: 40 }
stagger: 0.1s
duration: 0.6s
ease: power2.out
```

---

## 8. Dashboard Design Concept

### Visual Language: Dark Premium Admin

The admin dashboard mirrors the public site's palette but shifts from cinematic to **functional clarity**:

- Same `--color-void` background
- Sidebar: `--color-deep-navy` with `--color-border` right edge
- Content area: slightly lighter than sidebar — `#0f1923`
- Topbar: glassmorphism (`backdrop-filter: blur(16px)`)

### Layout

```
┌──────────┬────────────────────────────────────────────┐
│          │  TOPBAR: breadcrumb / user avatar / logout  │
│ SIDEBAR  ├────────────────────────────────────────────┤
│          │                                            │
│ Logo     │  PAGE TITLE                                │
│          │  [subtitle / last updated]                  │
│ Dashboard│                                            │
│ Pages    │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐     │
│ Posts    │  │ Stat │ │ Stat │ │ Stat │ │ Stat │     │
│ Projects │  └──────┘ └──────┘ └──────┘ └──────┘     │
│ Skills   │                                            │
│ Exp.     │  ┌─────────────────────┐ ┌─────────────┐  │
│ Media    │  │  Recent Activity    │ │  Quick      │  │
│ SEO      │  │                     │ │  Actions    │  │
│ Settings │  │                     │ │             │  │
│          │  └─────────────────────┘ └─────────────┘  │
│ [Logout] │                                            │
└──────────┴────────────────────────────────────────────┘
```

### Stat Cards

Four KPI cards in the top row:

| Metric | Icon | Accent |
|---|---|---|
| Total Posts | Document icon | Emerald |
| Total Projects | Grid icon | Blue-soft |
| Media Files | Image icon | Crimson |
| Contact Submissions | Inbox icon | Grey-300 |

Card structure:
- `background: --color-deep-navy`
- Large number `--text-display-2` in white
- Label `--text-sm` in grey-300
- Colored left border `4px solid var(--accent-color)`
- Icon `32px` in accent color, right-aligned

### Sidebar Design

- Width: `240px` desktop, collapses to icon-only `64px` on tablet
- Active item: crimson left border `3px` + white text + `--color-teal-dark` background
- Inactive item: grey-300 text, hover shifts to white
- Section dividers: `--color-border` horizontal line with section label `--text-xs` uppercase

### Data Tables (Posts, Projects, etc.)

- Full-width, `border-collapse: collapse`
- Row hover: `background: rgba(255,255,255,0.02)` — barely-there highlight
- Header row: `--color-border` bottom border, `--text-xs` uppercase grey-500 labels
- Action buttons per row: ghost icon buttons (edit pencil, delete trash) in grey-300, hover to white/crimson
- Status badges: pill design — Published = emerald, Draft = grey, Featured = crimson

### Form Design

- Labels: `--text-sm` grey-300, `margin-bottom: 6px`
- Inputs: `background: --color-void`, `border: 1px solid --color-border`, white text, `border-radius: 8px`, `padding: 10px 14px`
- Focus: `border-color: --color-crimson`, `box-shadow: 0 0 0 3px var(--color-crimson-glow)`
- Textareas: same treatment, `min-height: 120px`, `resize: vertical`
- Submit buttons: `--btn-primary` (crimson bg, white text, `8px` radius)

---

## 9. Animation Principles

### Timing Functions

| Token | Value | Usage |
|---|---|---|
| `--ease-out-expo` | `cubic-bezier(0.16, 1, 0.3, 1)` | Entries, reveals |
| `--ease-in-out` | `cubic-bezier(0.4, 0, 0.2, 1)` | State transitions |
| `--ease-spring` | GSAP `elastic.out(1, 0.5)` | Hover scale effects |

### Duration Standards

| Interaction | Duration |
|---|---|
| Micro (hover, focus) | 150–200ms |
| Component (modal open, drawer) | 300ms |
| Page element reveal | 500–700ms |
| Scene entry | 800–1200ms |

### Scroll-Driven Reveals

All `.reveal` elements animate in when entering the viewport:
- `opacity: 0 → 1`, `translateY: 32px → 0`
- Threshold: 20% of element visible
- Stagger for sibling elements: `0.08s` per child

### GSAP ScrollTrigger Landmarks

| Section | Trigger Action |
|---|---|
| Hero | Camera Z-pull-back on scroll out |
| About | Orbital rings slow to stop as section leaves |
| AI Projects | Node graph expands from center on enter |
| Experience | Timeline items stagger-reveal on scroll |
| Skills | Proficiency bars animate width on enter |
| Portfolio | Cards cascade in from bottom on enter |

---

## 10. Iconography & Media

### Icon System

- Source: **Heroicons** (MIT license) — consistent with the premium dark aesthetic
- Size: `20px` inline, `24px` standalone, `32px` feature
- Stroke width: `1.5px` (not bold — precision over weight)
- Color: inherits parent `currentColor`

### Image Treatment

- **Portrait images:** Displayed with subtle vignette overlay (`radial-gradient(circle, transparent 50%, rgba(8,12,16,0.4) 100%)`)
- **Project thumbnails:** `aspect-ratio: 16/9`, `object-fit: cover`, always in a `border-radius: 12px` container
- **Blog featured images:** `aspect-ratio: 3/2`, same treatment
- **No stock photos** anywhere — only authentic project screenshots or abstract 3D renders

### Logo Usage

- Full logo (globe + "S"): used in nav on light variant (inverted), footer
- "S" monogram only: favicon, small icon contexts
- Globe wireframe alone: decorative background motif in About section

---

## 11. Responsive Breakpoints

| Token | Breakpoint | Notes |
|---|---|---|
| Mobile | `< 640px` | Single column, reduced particle count (300→100) |
| Tablet | `640px – 1024px` | 2-col layouts, sidebar collapses |
| Desktop | `1024px – 1280px` | Primary layout target |
| Wide | `> 1280px` | Max container width caps at 1280px |

### Mobile-Specific Rules

- Three.js scenes hidden on `< 480px` (`display: none` on canvas) if performance risk
- Particle counts halved on mobile detection (`navigator.hardwareConcurrency <= 4`)
- Navigation collapses to hamburger with full-screen overlay
- Hero goes single-column: portrait above, text below
- Font scale uses `clamp()` so no separate mobile overrides needed

---

## 12. Voice & Tone

### Content Principles

| Principle | Description |
|---|---|
| **Confident, not arrogant** | First person, active voice. "I built" not "I was responsible for building." |
| **Precise, not vague** | Name the tech, name the number, name the outcome. No "various technologies." |
| **Human, not corporate** | Short sentences. Real words. No "leverage synergies." |
| **Future-oriented** | Frame experience as momentum: where you're going, not just where you've been. |

### Sample Copy Patterns

**Section eyebrows:** `WHAT I BUILD` / `WHERE I'VE WORKED` / `THE CRAFT` / `LET'S CONNECT`

**Hero tagline formula:** `[Verb] [artifact] that [concrete outcome].`  
Example: *"Building AI systems that turn messy data into clear decisions."*

**Project descriptions:** Lead with the problem, land on the impact. 2 sentences max.

**CTA text:** Action verbs only. `View Project` / `Read Case Study` / `Get in Touch` / `See All Work`

---

*End of DESIGN_SYSTEM.md*
