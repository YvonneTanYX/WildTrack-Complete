<?php
/*require_once __DIR__ . '/check_session.php';*/
$currentPage = 'visit'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="hero.css"/>
  <link rel="stylesheet" href="shared.css">
  <script src="mainPage.js"></script>
  <title>Zoo Map — WildTrack Zoo</title>
  <style>
    /* ── Download card ── */
    .download-card {
      display: flex;
      align-items: center;
      gap: 20px;
      background: #fff;
      border-radius: 12px;
      padding: 20px 24px;
      max-width: 420px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.07);
      margin: 20px 0;
    }
    .download-card img {
      width: 90px;
      border-radius: 6px;
      object-fit: cover;
    }
    .download-card p {
      font-size: 14px;
      color: #666;
      margin: 4px 0 10px;
    }

    /* ── Interactive map wrapper ── */
    #zoo-map-wrap {
      font-family: system-ui, sans-serif;
      max-width: 900px;
      margin: 0 auto;
    }

    #map-container {
      position: relative;
      width: 100%;
      line-height: 0;
      border-radius: 12px;
      overflow: hidden;
    }

    #map-img {
      width: 100%;
      display: block;
      border-radius: 12px;
    }

    /* ── Pin buttons ── */
    .pin-btn {
      position: absolute;
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      transform: translate(-50%, -100%);
      transition: filter 0.15s;
    }
    .pin-btn:hover { filter: brightness(1.15); }
    .pin-btn.active { filter: drop-shadow(0 0 6px currentColor); }

    /* ── Detail panel ── */
    #detail-panel {
      margin-top: 10px;
      padding: 14px 18px;
      border-radius: 12px;
      border-left: 4px solid #ddd;
      background: #fafafa;
      min-height: 52px;
      transition: background 0.2s;
    }
    #detail-panel .pin-name {
      font-weight: 700;
      font-size: 15px;
      margin-bottom: 4px;
    }
    #detail-panel .pin-desc {
      font-size: 13px;
      color: #555;
      margin-bottom: 10px;
    }
    #detail-panel .pin-placeholder {
      font-size: 13px;
      color: #bbb;
    }

    /* ── Animal chips ── */
    .animals-row {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      margin-top: 8px;
      background: none;
      border: none;
      padding: 0;
      cursor: default;
    }
    .animal-chip {
      font-size: 13px;
      padding: 3px 10px;
      border-radius: 20px;
      background: #fff;
      text-decoration: none;
    }
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<!-- HERO — same as zooMap_old.php -->
<section class="hero">
  <img class="hero-img"
    src="https://images.unsplash.com/photo-1480044965905-02098d419e96?q=80&w=2070&auto=format&fit=crop"
    alt="Zoo"/>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <p class="hero-eyebrow">WildTrack Malaysia</p>
    <h1 class="hero-title">Zoo<em> Map</em></h1>
    <p class="hero-sub">Explore the Wild: Your Interactive Zoo Guide</p>
  </div>
  <div class="paw1 paw">🐾</div>
  <div class="paw2 paw">🐾</div>
  <div class="paw3 paw">🐾</div>
</section>

<div class="content-section">

  <h2>Find your way around WildTrack Zoo</h2>
  <p>Use the map below to plan your visit. Click any pin to see what's in that zone,
     or print the map and bring it with you.</p>

  <!-- ── Interactive pin map ── -->
  <div id="zoo-map-wrap">
    <div id="map-container">
      <img id="map-img" alt="Zoo map" />
      <!-- pins injected by JS -->
    </div>

    <div id="detail-panel">
      <p class="pin-placeholder">Click a pin to see details</p>
    </div>
  </div>

  <!-- ── Download card ── -->
  <h2 style="color:#2a5a2e; margin-top:32px;">Download the Map</h2>
  <p>Take the map with you — print it before your visit or save it to your phone.</p>

  <div class="download-card">
    <img id="download-img" alt="Map thumbnail">
    <div>
      <strong>WildTrack Zoo Map</strong>
      <p>JPEG format, easy to print</p>
      <a id="download-link" download class="download-link">⬇ Download Map</a>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>

<script>
  window.breadcrumb = [
    { label: 'Visit', href: 'visitMain.php' },
    { label: 'Zoo Map' }
  ];
</script>
<script src="FinalProject.js"></script>
<script>
  // ── State ──────────────────────────────────────────────────────────────────
  let pins     = [];
  let activeId = null;

  // ── Fetch map data ─────────────────────────────────────────────────────────
  fetch("/WildTrack/api/MapData.php")
    .then(res => res.json())
    .then(data => {
      const mapSrc = data.Map ?? '';
      pins = (Array.isArray(data.Pins) ? data.Pins : Object.values(data.Pins || {}))
        .map(p => ({
          ...p,
          pos: p.pos ?? { x: parseFloat(p.pos_x ?? 0), y: parseFloat(p.pos_y ?? 0) }
        }));

      if (mapSrc) {
        // Set map image everywhere
        document.getElementById("map-img").src        = mapSrc;
        document.getElementById("download-img").src   = mapSrc;
        document.getElementById("download-link").href = mapSrc;
      } else {
        // No map uploaded yet — show a placeholder message
        document.getElementById("map-container").innerHTML =
          '<div style="padding:40px;text-align:center;color:#aaa;border:2px dashed #ddd;border-radius:12px;">' +
          '🗺️ Map not set yet. Ask admin to upload one.</div>';
        document.getElementById("download-img").style.display   = 'none';
        document.getElementById("download-link").style.display  = 'none';
      }

      renderPins();
    })
    .catch(() => {
      document.getElementById("map-container").innerHTML =
        '<div style="padding:40px;text-align:center;color:#c33;border:2px dashed #fcc;border-radius:12px;">' +
        '⚠️ Could not load map data. Please try refreshing.</div>';
    });

  // ── Build the teardrop SVG pin ─────────────────────────────────────────────
  function makePinSVG(color, emoji) {
    return `
      <svg width="32" height="43" viewBox="0 0 40 54"
        style="filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));display:block;">
        <path d="M20 2C11.16 2 4 9.16 4 18c0 12 16 34 16 34s16-22 16-34C36 9.16 28.84 2 20 2z"
          fill="${color}" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
        <circle cx="20" cy="18" r="10" fill="white" opacity="0.92"/>
        <text x="20" y="23" text-anchor="middle" font-size="11">${emoji}</text>
      </svg>`;
  }

  // ── Render all pins onto the map ───────────────────────────────────────────
  function renderPins() {
    const container = document.getElementById("map-container");
    container.querySelectorAll(".pin-btn").forEach(el => el.remove());

    pins.forEach(pin => {
      const btn       = document.createElement("button");
      btn.className   = "pin-btn";
      btn.id          = "pin-" + pin.id;
      btn.title       = pin.name;
      btn.style.left  = pin.pos.x + "%";
      btn.style.top   = pin.pos.y + "%";
      btn.innerHTML   = makePinSVG(pin.color, pin.emoji);
      btn.onclick     = () => setActive(pin.id);
      container.appendChild(btn);
    });
  }

  // ── Activate pin & show detail panel ──────────────────────────────────────
 function setActive(id) {
  if (activeId) {
    const prev = document.getElementById("pin-" + activeId);
    if (prev) { prev.classList.remove("active"); prev.style.filter = ''; }
  }
  activeId = id;
  const pin = pins.find(p => p.id === id);
  if (!pin) return;
  const btn = document.getElementById("pin-" + id);
  if (btn) {
    btn.classList.add("active");
    btn.style.filter = `drop-shadow(0 0 6px ${pin.color})`;
  }
  showPanelLoading(pin);
  
  // Fetch animals and update panel, then scroll to it
  // AFTER (matches MapData.php's ?animals_by_zone= handler)
fetch(`/WildTrack/api/MapData.php?animals_by_zone=${encodeURIComponent(pin.zone)}`)    .then(res => res.json())
    .then(data => {
      updatePanel(pin, data.animals ?? []);
      // Scroll to detail panel after content is loaded
      const panel = document.getElementById("detail-panel");
      if (panel) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
}
  function showPanelLoading(pin) {
    const panel = document.getElementById("detail-panel");
    panel.style.background  = pin.light ?? '#fafafa';
    panel.style.borderColor = pin.color;
    panel.innerHTML = `
      <div class="pin-name" style="color:${pin.color}">${pin.emoji} ${pin.name}</div>
      <div style="font-size:12px;color:#999;margin-bottom:6px;">Zone: ${pin.zone}</div>
      <div class="pin-desc">${pin.desc ?? ''}</div>
      <div style="color:#bbb;font-size:13px;">Loading animals…</div>`;
  }

 function updatePanel(pin, animals) {
  const panel = document.getElementById("detail-panel");
  panel.style.background  = pin.light ?? '#fafafa';
  panel.style.borderColor = pin.color;
  
function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
  const chipsHTML = animals.length > 0
    ? `<div class="animals-row">
        ${animals.map(a => {
          // a can be a string (animal name) or an object with name property
          const animalName = typeof a === 'string' ? a : (a.emoji ? a.emoji + ' ' + a.name : a.name);
          return `<span class="animal-chip"
            style="color:${pin.color};border:1px solid ${pin.color}44; cursor:default;">
            ${escapeHtml(animalName)}
          </span>`;
        }).join('')}
      </div>`
    : `<div style="font-size:13px;color:#bbb;margin-top:8px;">No animals listed for this zone.</div>`;

  panel.innerHTML = `
    <div class="pin-name" style="color:${pin.color}">${pin.emoji} ${escapeHtml(pin.name)}</div>
    <div style="font-size:12px;color:#999;margin-bottom:6px;">Zone: ${escapeHtml(pin.zone)}</div>
    <div class="pin-desc">${escapeHtml(pin.desc ?? '')}</div>
    ${chipsHTML}`;
}
</script>
</body>
</html>
