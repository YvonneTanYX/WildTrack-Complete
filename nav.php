<?php
/**
 * nav.php — Shared navigation for all WildTrack visitor pages.
 * Requires check_session.php to have already run (session must be active).
 * Usage: include 'nav.php';  (after require_once 'check_session.php')
 */
if (!isset($currentPage)) $currentPage = '';
$loggedInUser = $_SESSION['user'] ?? null;
$isGuest      = !$loggedInUser;
$displayName  = $loggedInUser ? htmlspecialchars($loggedInUser['username']) : 'Guest';
?>
<nav>
  <div class="container-header">
    <ul class="nav-list">

      <li class="logo">
        <a href="mainPage.php">
          <img src="Logo_Green.png" width="90" height="90" alt="WildTrack Zoo">
        </a>
      </li>

      <!-- VISIT -->
      <li class="dropdown <?php echo $currentPage==='visit'?'active-section':''; ?>" aria-expanded="false">
        <a href="visitMain.php" class="dropbutton">Visit ▾</a>
        <div class="dropdown-menu">
          <div class="left-column">
            <div class="image-container-visit">
              <h2 class="image-text-visit">Visit</h2>
              <a href="visitMain.php"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTA5f_frFRYSM6PSi3YDwW9T7IAu16vWNPl5qmYno5Xg28xHhpn2xLpEsNVh1_MXvfuNQzz_OVufFSTEjj5L5_VikHXcT1vJL80Ip2RGAM&s=10" alt="Visit"></a>
            </div>
          </div>
          <div class="right-column">
            <a href="openingHourRate.php">Opening Hours &amp; Rates</a>
            <a href="zooMap.php">Zoo Map</a>
            <a href="accessibility.php">Accessibility at the Zoo</a>
            <a href="buyTicket.php">Buy a Ticket</a>
            <a href="foodAndDrink.php">Food and Drink</a>
            <a href="animalFeeding.php">Animal Feeding</a>
          </div>
        </div>
      </li>

      <!-- THINGS TO DO -->
      <li class="dropdown <?php echo $currentPage==='things'?'active-section':''; ?>" aria-expanded="false">
        <a href="thingMain.php" class="dropbutton">Things to Do ▾</a>
        <div class="dropdown-menu">
          <div class="left-column">
            <div class="image-container-thingToDo">
              <h2 class="image-text-thingToDo">Things to Do</h2>
              <a href="thingMain.php"><img src="https://plus.unsplash.com/premium_photo-1673296129756-e45408e25250?q=80&w=1113&auto=format&fit=crop&ixlib=rb-4.1.0" alt="Things to Do"></a>
            </div>
          </div>
          <div class="right-column">
            <a href="event.php">Events &amp; Talk Times</a>
            <a href="learning.php">Learning</a>
            <a href="birthdayParties.php">Birthday Parties</a>
          </div>
        </div>
      </li>

      <!-- ANIMAL -->
      <li class="dropdown <?php echo $currentPage==='animal'?'active-section':''; ?>" aria-expanded="false">
        <a href="animalMain.php" class="dropbutton">Animal ▾</a>
        <div class="dropdown-menu">
          <div class="left-column">
            <div class="image-container-animal">
              <h2 class="image-text-animal">Animal</h2>
              <a href="animalMain.php"><img src="https://images.unsplash.com/photo-1605092676920-8ac5ae40c7c8?q=80&w=1065&auto=format&fit=crop&ixlib=rb-4.1.0" alt="Animals"></a>
            </div>
          </div>
          <div class="right-column">
            <a href="recognition.php">Animal Recognition</a>
            <a href="meetTheAnimals.php">Meet the Animals</a>
          </div>
        </div>
      </li>

      <!-- CONSERVATION -->
      <li class="dropdown <?php echo $currentPage==='conservation'?'active-section':''; ?>" aria-expanded="false">
        <a href="conservationMain.php" class="dropbutton">Conservation ▾</a>
        <div class="dropdown-menu">
          <div class="left-column">
            <div class="image-container-conservation">
              <h2 class="image-text-conservation">Conservation</h2>
              <a href="conservationMain.php"><img src="https://images.unsplash.com/photo-1757947100964-5bd95456867e?q=80&w=2107&auto=format&fit=crop&ixlib=rb-4.1.0" alt="Conservation"></a>
            </div>
          </div>
          <div class="right-column">
            <a href="savingWildlife.php">Saving Wildlife</a>
            <a href="greenZooGreenYou.php">Green Zoo, Green You</a>
            <a href="safeCatSafeWildlife.php">Safe Cat, Safe Wildlife</a>
          </div>
        </div>
      </li>

      <!-- GET IN TOUCH -->
      <li class="dropdown <?php echo $currentPage==='contact'?'active-section':''; ?>" aria-expanded="false">
        <a href="getInTouch.php" class="dropbutton">Get in Touch ▾</a>
        <div class="dropdown-menu">
          <div class="left-column">
            <div class="image-container-getInTouch">
              <h2 class="image-text-getInTouch">Get in Touch</h2>
              <a href="getInTouch.php"><img src="https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?q=80&w=987&auto=format&fit=crop&ixlib=rb-4.1.0" alt="Get in Touch"></a>
            </div>
          </div>
          <div class="right-column">
            <a href="getInTouch.php#general">Enquiries &amp; Feedback</a>
          </div>
        </div>
      </li>

      <!-- BUY TICKETS button
           Guests   → buyTicket.php  (they see prices + locked button there)
           Logged in → Ticketing.php (go straight to booking) -->
      <li style="list-style:none; display:flex; align-items:center; margin-left:auto; padding-right:6px;">
        <?php if ($isGuest): ?>
          <a href="Ticketing.php" class="wt-nav-buy-btn">🎟 Buy Tickets</a>
        <?php else: ?>
          <a href="Ticketing.php" class="wt-nav-buy-btn">🎟 Buy Tickets</a>
        <?php endif; ?>
      </li>

      <!-- USER MENU -->
      <li class="dropdown" aria-expanded="false" style="margin-left:0;">
        <a href="#" class="dropbutton" style="display:flex;align-items:center;gap:6px;">
          <span style="font-size:18px;">👤</span> <?php echo $displayName; ?> ▾
        </a>
        <div class="dropdown-menu" style="min-width:200px;left:auto;right:0;transform:none;">
          <div class="right-column" style="margin-left:0;">
            <?php if ($isGuest): ?>
              <!--
                login.html?tab=login   → JS reads ?tab= and opens sign-in directly
                login.html?tab=signup  → JS reads ?tab= and opens register directly
                Both skip the portal chooser entirely (no ?reason= so no banner either,
                but we add ?portal=visitor so the visitor step opens straight away).
              -->
              <a href="login.html?portal=visitor&tab=login">🔑 Login</a>
              <a href="login.html?portal=visitor&tab=signup">📝 Register</a>
            <?php else: ?>
              <a href="MyTickets.php">🎟️ Check Ticket</a>
              <a href="MyBookings.php">📅 Booking Status</a>
              <a href="#" onclick="doLogout();return false;">🚪 Sign Out</a>
            <?php endif; ?>
          </div>
        </div>
      </li>

      <!-- NOTIFICATION BELL -->
      <li style="list-style:none; display:flex; align-items:center; padding-left:8px;">
        <div class="wt-nav-bell" id="wt-bell-btn" onclick="wtToggleNotifDropdown()" title="Notifications">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="#2D5A27" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <span class="wt-notif-badge" id="wt-notif-badge"></span>
        </div>
      </li>

      <!-- ANIMAL RECOGNITION camera icon -->
      <li style="list-style:none; display:flex; align-items:center; padding-left:8px;">
        <div class="wt-nav-bell" onclick="window.location.href='recognition.php'" title="Animal Recognition" style="cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="#2D5A27" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
            <circle cx="12" cy="13" r="4"/>
          </svg>
        </div>
      </li>

    </ul>
  </div>
</nav>

<!-- Breadcrumb bar -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <span id="breadcrumb-trail"></span>
</nav>

<script>
<?php if (!$isGuest): ?>
async function doLogout() {
    if (!confirm('Are you sure you want to sign out?')) return;
    try {
        await fetch('api/auth.php?action=logout', { method: 'POST', credentials: 'include' });
    } catch (e) {
        console.error('Logout failed:', e);
    } finally {
        window.location.href = 'login.html?portal=visitor&tab=login';
    }
}
<?php else: ?>
function doLogout() { /* guest — no-op */ }
<?php endif; ?>
</script>

<!-- NOTIFICATION DROPDOWN -->
<div class="wt-notif-dropdown" id="wt-notif-dropdown">
  <div class="wt-notif-dropdown-header">
    <span>Notifications</span>
    <button id="wt-mark-all-btn"
            onclick="wtMarkAllRead()"
            style="background:none;border:none;font-size:12px;font-weight:600;color:#2D5A27;cursor:pointer;padding:0;margin-left:auto;">
      Mark all read
    </button>
  </div>
  <div id="wt-notif-list">
    <div class="wt-notif-empty">No notifications yet</div>
  </div>
</div>

<!-- TOAST -->
<div class="wt-toast" id="wt-toast"></div>

<style>
.wt-nav-buy-btn {
  display: inline-flex; align-items: center; gap: 6px;
  background: #b59f75; color: #a0ff94 !important;
  font-size: 14px; font-weight: 700; padding: 9px 20px; border-radius: 20px;
  text-decoration: none !important; letter-spacing: 0.4px;
  border: 2px solid #ffffff;
  transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
  white-space: nowrap; box-shadow: 0 2px 8px rgba(0,0,0,0.18);
}
.wt-nav-buy-btn:hover {
  background: #b8924e; transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(0,0,0,0.22);
  color: #1a3d18 !important; text-decoration: none !important;
}
.wt-nav-bell, a.wt-nav-bell {
  width: 40px; height: 40px; border-radius: 50%;
  background: #fff; border: 1px solid #e4e9e0;
  display: flex !important; align-items: center; justify-content: center;
  cursor: pointer; box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  position: relative; transition: background 0.2s, border-color 0.2s;
  text-decoration: none;
}
.wt-nav-bell:hover, a.wt-nav-bell:hover { background: #f0f4ee; border-color: #2D5A27; }
.wt-notif-badge {
  position: absolute; top: -4px; right: -4px;
  width: 18px; height: 18px; background: #E74C3C;
  border-radius: 50%; border: 2px solid #fff;
  font-size: 10px; font-weight: 700; color: #fff;
  display: none; align-items: center; justify-content: center;
}
.wt-notif-dropdown {
  position: fixed; top: 72px; right: 20px; width: 320px;
  background: #fff; border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  border: 1px solid #e4e9e0; z-index: 9999;
  display: none; overflow: hidden;
}
.wt-notif-dropdown.open { display: block; }
.wt-notif-dropdown-header {
  padding: 14px 20px 12px;
  font-size: 14px; font-weight: 700; color: #2F3640;
  border-bottom: 1px solid #f0f4ee;
  display: flex; align-items: center; justify-content: space-between;
}
.wt-notif-item { padding: 14px 20px; border-bottom: 1px solid #f0f4ee; cursor: pointer; transition: background 0.15s; display: flex; gap: 10px; align-items: flex-start; }
.wt-notif-item:last-child { border-bottom: none; }
.wt-notif-item:hover { background: #f7faf5; }
.wt-notif-item.wt-notif-unread { background: #f0f7ee; }
.wt-notif-item.wt-notif-unread:hover { background: #e6f2e3; }
.wt-notif-item-dot { width: 8px; height: 8px; background: #2D5A27; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.wt-notif-item-title { font-size: 13px; font-weight: 600; color: #2F3640; margin-bottom: 3px; }
.wt-notif-item-body  { font-size: 12px; color: #7F8C8D; }
.wt-notif-empty { padding: 24px 20px; text-align: center; font-size: 13px; color: #aaa; }
.wt-toast {
  position: fixed; bottom: 80px; left: 50%;
  transform: translateX(-50%) translateY(20px);
  background: #2D5A27; color: #fff; padding: 14px 24px;
  border-radius: 16px; font-size: 14px; font-weight: 600;
  box-shadow: 0 8px 24px rgba(45,90,39,0.35);
  z-index: 99999; opacity: 0; transition: all 0.35s ease;
  pointer-events: none; text-align: center; max-width: 340px; width: 90%;
}
.wt-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>

<script>
(function () {
  var wtToastTimer = null;
  var _wtNotifications = [];  // cache for mark-all-read

  function wtEscHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function wtShowToast(msg, duration) {
    duration = duration || 3500;
    var toast = document.getElementById('wt-toast');
    toast.textContent = msg;
    toast.classList.add('show');
    if (wtToastTimer) clearTimeout(wtToastTimer);
    wtToastTimer = setTimeout(function () { toast.classList.remove('show'); }, duration);
  }

  function wtRenderNotifDropdown(notifications) {
    _wtNotifications = notifications || [];
    var listEl = document.getElementById('wt-notif-list');
    if (!notifications || notifications.length === 0) {
      listEl.innerHTML = '<div class="wt-notif-empty">No notifications yet</div>';
      // Hide mark all read button when empty
      var btn = document.getElementById('wt-mark-all-btn');
      if (btn) btn.style.display = 'none';
      return;
    }
    // Show mark all read if there are unread items
    var hasUnread = notifications.some(function(n) { return !parseInt(n.is_read); });
    var btn = document.getElementById('wt-mark-all-btn');
    if (btn) btn.style.display = hasUnread ? '' : 'none';

    listEl.innerHTML = '';
    notifications.forEach(function (n) {
      var isUnread = !parseInt(n.is_read);
      var item = document.createElement('div');
      item.className = 'wt-notif-item' + (isUnread ? ' wt-notif-unread' : '');
      item.innerHTML =
        (isUnread ? '<div class="wt-notif-item-dot"></div>' : '<div style="width:8px;flex-shrink:0;"></div>') +
        '<div style="flex:1;min-width:0;">' +
          '<div class="wt-notif-item-title">' + wtEscHtml(n.title) + '</div>' +
          '<div class="wt-notif-item-body">'  + wtEscHtml(n.body)  + '</div>' +
        '</div>';
      item.onclick = function () {
        wtMarkNotifRead(n.id);
        item.classList.remove('wt-notif-unread');
        item.querySelector('.wt-notif-item-dot')?.remove();
        wtCloseNotifDropdown();
        if (n.type === 'booking_approved')  window.location.href = 'MyTickets.php';
        if (n.type === 'feedback_reply')    window.location.href = 'getInTouch.php';
      };
      listEl.appendChild(item);
    });
  }

  async function wtMarkNotifRead(notifId) {
    try {
      await fetch('api/notifications.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: [notifId] })
      });
    } catch (e) {}
  }

  // FIX 3 — Mark All Read for visitor
  window.wtMarkAllRead = async function () {
    var unreadIds = _wtNotifications
      .filter(function(n) { return !parseInt(n.is_read); })
      .map(function(n) { return n.id; });
    if (!unreadIds.length) return;
    try {
      await fetch('api/notifications.php?action=mark_read', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: unreadIds })
      });
      // Update UI immediately
      var badge = document.getElementById('wt-notif-badge');
      if (badge) { badge.style.display = 'none'; badge.textContent = ''; }
      var btn = document.getElementById('wt-mark-all-btn');
      if (btn) btn.style.display = 'none';
      document.querySelectorAll('.wt-notif-unread').forEach(function(el) {
        el.classList.remove('wt-notif-unread');
        var dot = el.querySelector('.wt-notif-item-dot');
        if (dot) dot.remove();
      });
      wtShowToast('All notifications marked as read ✓');
    } catch (e) {}
  };

  async function wtPollNotifications() {
    <?php if ($isGuest): ?>
    return; // guests have no notifications
    <?php endif; ?>
    try {
      // FIX 1 — use the correct visitor notifications endpoint
      var res  = await fetch('api/notifications.php', { credentials: 'include' });
      var data = await res.json();
      if (!data.success) return;
      var notifications = data.notifications || [];
      var unread = notifications.filter(function (n) { return !parseInt(n.is_read); });
      var count  = unread.length;
      var badge  = document.getElementById('wt-notif-badge');
      if (badge) {
        badge.style.display = count > 0 ? 'flex' : 'none';
        badge.textContent   = count > 0 ? count : '';
      }

      // Toast for newly approved bookings (only once per session)
      var shownKey = 'wt_shown_notifs';
      var shownRaw = '';
      try { shownRaw = sessionStorage.getItem(shownKey) || ''; } catch (e) {}
      var shownIds = shownRaw ? shownRaw.split(',').filter(Boolean) : [];
      notifications.forEach(function (n) {
        var idStr = String(n.id);
        if (n.type === 'booking_approved' && !parseInt(n.is_read) && shownIds.indexOf(idStr) === -1) {
          wtShowToast('🎉 Booking ' + (n.booking_ref || '') + ' approved! Tap the bell to view your QR ticket.', 6000);
          shownIds.push(idStr);
        }
        if (n.type === 'feedback_reply' && !parseInt(n.is_read) && shownIds.indexOf(idStr) === -1) {
          wtShowToast('💬 The zoo replied to your feedback!', 4000);
          shownIds.push(idStr);
        }
      });
      try { sessionStorage.setItem(shownKey, shownIds.join(',')); } catch (e) {}

      wtRenderNotifDropdown(notifications);
    } catch (e) {}
  }

  window.wtToggleNotifDropdown = function () {
    var dd = document.getElementById('wt-notif-dropdown');
    dd.classList.toggle('open');
  };
  window.wtCloseNotifDropdown  = function () {
    document.getElementById('wt-notif-dropdown').classList.remove('open');
  };

  document.addEventListener('click', function (e) {
    var dd   = document.getElementById('wt-notif-dropdown');
    var bell = e.target.closest('#wt-bell-btn');
    if (dd && !bell && !dd.contains(e.target)) dd.classList.remove('open');
  });

  window.addEventListener('load', function () {
    wtPollNotifications();
    setInterval(wtPollNotifications, 30000);
  });
})();
</script>
