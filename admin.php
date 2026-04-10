<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Redirect non-admins away
$_u = $_SESSION['user'] ?? null;
if (!$_u || $_u['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WildTrack Admin</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap');

:root {
    --green-dark: #2D5A27;
    --green-mid: #3E7A34;
    --green-light: #5A9E4E;
    --green-pale: #EAF1E8;
    --green-bg: #F2F5F0;
    --amber: #D4872A;
    --amber-light: #FFF3E0;
    --orange: #C9541E;
    --orange-light: #FBE9E0;
    --purple: #6B52A3;
    --purple-light: #F0ECFB;
    --blue: #2563EB;
    --blue-light: #EFF6FF;
    --red: #DC2626;
    --text-dark: #1A2B18;
    --text-mid: #3D5234;
    --text-muted: #7A9170;
    --text-light: #B8CEB4;
    --white: #FFFFFF;
    --border: #D8E8D4;
    --sidebar-w: 240px;
    --topbar-h: 60px;
    --radius: 14px;
    --radius-sm: 10px;
    --shadow: 0 2px 12px rgba(30, 60, 25, 0.08);
    --shadow-lg: 0 8px 32px rgba(30, 60, 25, 0.14);
    --transition: 0.2s ease;
}

*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html {
    font-size: 13px;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--green-bg);
    color: var(--text-dark);
    min-height: 100vh;
    display: flex;
    overflow-x: clip;
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-light);
}

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: var(--sidebar-w);
    background: var(--green-dark);
    display: flex;
    flex-direction: column;
    z-index: 200;
    transition: transform var(--transition);
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 22px 22px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-icon {
    width: 46px;
    height: 46px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}

.brand-icon svg {
    width: 28px;
    height: 28px;
}

.brand-name {
    display: block;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.3px;
}

.brand-sub {
    display: block;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 2px;
}

.sidebar-nav {
    padding: 12px 10px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.nav-section-label {
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.35);
    padding: 8px 10px 5px;
    font-weight: 700;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 12px;
    border-radius: 11px;
    color: rgba(255, 255, 255, 0.65);
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: all var(--transition);
    cursor: pointer;
    position: relative;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.nav-item.active {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    font-weight: 700;
}

.nav-icon {
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.nav-icon svg {
    width: 20px;
    height: 20px;
}

.nav-badge {
    margin-left: auto;
    background: var(--orange);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-card {
    display: flex;
    align-items: center;
    gap: 11px;
    flex: 1;
    cursor: pointer;
    border-radius: 10px;
    padding: 8px 10px;
    transition: background var(--transition);
}

.user-card:hover {
    background: rgba(255, 255, 255, 0.08);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--green-light);
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-name {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
}

.user-role {
    display: block;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    margin-top: 1px;
}

.logout-btn {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    color: rgba(255, 255, 255, 0.5);
    transition: all var(--transition);
    flex-shrink: 0;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.logout-btn svg {
    width: 20px;
    height: 20px;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: margin-left var(--transition);
}

.topbar {
    height: var(--topbar-h);
    background: var(--white);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    position: sticky;
    top: 0;
    z-index: 50;
    gap: 16px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.sidebar-toggle {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    padding: 7px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
}

.sidebar-toggle:hover {
    background: var(--green-pale);
    color: var(--green-dark);
}

.sidebar-toggle svg {
    width: 22px;
    height: 22px;
}

.page-breadcrumb {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
}

.live-badge {
    font-size: 12px;
    font-weight: 700;
    color: var(--green-light);
    background: var(--green-pale);
    padding: 4px 12px;
    border-radius: 20px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
}

.search-bar {
    display: flex;
    align-items: center;
    gap: 9px;
    background: var(--green-bg);
    border: 1px solid var(--border);
    border-radius: 11px;
    padding: 9px 16px;
}

.search-bar svg {
    width: 17px;
    height: 17px;
    color: var(--text-muted);
    flex-shrink: 0;
}

.search-bar input {
    border: none;
    background: none;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    color: var(--text-dark);
    outline: none;
    width: 180px;
}

.search-bar input::placeholder {
    color: var(--text-light);
}

.icon-btn {
    width: 42px;
    height: 42px;
    border: 1px solid var(--border);
    border-radius: 11px;
    background: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--text-mid);
    position: relative;
    transition: all var(--transition);
}

.icon-btn:hover {
    background: var(--green-pale);
    border-color: var(--green-light);
}

.icon-btn svg {
    width: 20px;
    height: 20px;
}

.notif-dot {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--orange);
    border: 2px solid var(--white);
}

/* NOTIFICATION PANEL */
.notif-panel {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 320px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    display: none;
    flex-direction: column;
    z-index: 200;
    overflow: hidden;
    max-height: 480px;
}

.notif-panel.open {
    display: flex;
}

.notif-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    font-size: 15px;
    flex-shrink: 0;
}

.notif-header button {
    background: none;
    border: none;
    font-size: 12px;
    color: var(--green-mid);
    cursor: pointer;
    font-weight: 600;
}

.notif-list {
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow-y: auto;
    flex: 1;
}

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 9px 12px;
    border-radius: 9px;
    font-size: 13px;
    transition: background var(--transition);
}

.notif-item:hover {
    background: var(--green-bg);
}

.notif-item.unread {
    background: var(--green-pale);
}

.notif-item strong {
    display: block;
    margin-bottom: 2px;
}

.notif-item small {
    color: var(--text-muted);
    font-size: 12px;
}

.notif-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notif-icon svg {
    width: 18px;
    height: 18px;
}

.notif-icon.green {
    background: var(--green-pale);
    color: var(--green-dark);
}

.notif-icon.orange {
    background: var(--orange-light);
    color: var(--orange);
}

.notif-icon.blue {
    background: var(--blue-light);
    color: var(--blue);
}

/* ===== PAGES ===== */
.page {
    display: none;
    padding: 20px;
    flex: 1;
}

.page.active {
    display: block;
}

/* ===== PAGE HEADER ===== */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 18px;
    gap: 16px;
    flex-wrap: wrap;
}

.page-header h1 {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
}

.page-header p {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 5px;
}

/* ===== BUTTONS ===== */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all var(--transition);
    white-space: nowrap;
}

.btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.btn-primary {
    background: var(--green-dark);
    color: #fff;
}

.btn-primary:hover {
    background: var(--green-mid);
}

.btn-outline {
    background: var(--white);
    color: var(--green-dark);
    border: 1.5px solid var(--border);
}

.btn-outline:hover {
    background: var(--green-pale);
    border-color: var(--green-light);
}

.btn-approve {
    background: var(--green-dark);
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-approve:hover {
    background: var(--green-mid);
}

.btn-reject {
    background: none;
    color: var(--red);
    border: 1.5px solid #FECACA;
    padding: 8px 16px;
    border-radius: 8px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-reject:hover {
    background: #FEE2E2;
}

.btn-reject-sm {
    background: none;
    color: var(--red);
    border: 1.5px solid #FECACA;
    padding: 6px 14px;
    border-radius: 7px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-reject-sm:hover {
    background: #FEE2E2;
}

.btn-edit {
    background: var(--green-pale);
    color: var(--green-dark);
    border: 1.5px solid var(--border);
    padding: 7px 16px;
    border-radius: 7px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-edit:hover {
    background: var(--border);
}

.btn-edit-full {
    width: 100%;
    background: var(--green-bg);
    color: var(--text-mid);
    border: 1.5px solid var(--border);
    padding: 9px;
    border-radius: 9px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition);
    margin-top: 10px;
}

.btn-edit-full:hover {
    background: var(--green-pale);
    border-color: var(--green-light);
    color: var(--green-dark);
}

.btn-outline-full {
    width: 100%;
    background: none;
    color: var(--green-dark);
    border: 1.5px dashed var(--border);
    padding: 11px;
    border-radius: 9px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 4px;
    transition: all var(--transition);
}

.btn-outline-full:hover {
    background: var(--green-pale);
    border-color: var(--green-light);
}

.btn-view-proof {
    background: var(--blue-light);
    color: var(--blue);
    border: none;
    padding: 6px 14px;
    border-radius: 7px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-view-proof:hover {
    background: #DBEAFE;
}

/* ===== BOOKING DETAILS MODAL ===== */
.bd-info-box {
  background: var(--green-bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 10px 14px;
}
.bd-info-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: var(--text-muted);
  margin-bottom: 4px;
}
.bd-info-val {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-dark);
  line-height: 1.4;
}
.bd-section-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: var(--text-muted);
  margin-bottom: 8px;
}
.bd-breakdown-box {
  background: var(--green-bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.bd-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: var(--text-dark);
}
.bd-total-row {
  border-top: 1px solid var(--border);
  padding-top: 8px;
  margin-top: 4px;
  font-weight: 700;
  font-size: 14px;
  color: var(--green-dark);
}
/* Clickable rows in approval table */
#approvalTableBody tr:hover td {
  background: var(--green-pale);
}

.btn-page {
    background: var(--white);
    border: 1.5px solid var(--border);
    padding: 8px 18px;
    border-radius: 8px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    cursor: pointer;
    transition: all var(--transition);
    color: var(--text-mid);
}

.btn-page:hover:not([disabled]) {
    background: var(--green-pale);
}

.btn-page[disabled] {
    opacity: 0.4;
    cursor: not-allowed;
}

.icon-btn-sm {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 17px;
    line-height: 1;
    padding: 2px 6px;
    border-radius: 4px;
}

.icon-btn-sm:hover {
    background: var(--green-bg);
}

/* ===== CARDS ===== */
.card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px 12px;
    gap: 12px;
    flex-wrap: wrap;
}

.card-header h3 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-dark);
}

.card-link {
    font-size: 13px;
    font-weight: 600;
    color: var(--green-mid);
    text-decoration: none;
    cursor: pointer;
}

.card-link:hover {
    color: var(--green-dark);
}

/* ===== STAT CARDS ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}

.stat-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 16px;
}

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
}

.stat-icon.green {
    background: var(--green-pale);
    color: var(--green-dark);
}

.stat-icon.amber {
    background: var(--amber-light);
    color: var(--amber);
}

.stat-icon.orange {
    background: var(--orange-light);
    color: var(--orange);
}

.stat-icon.purple {
    background: var(--purple-light);
    color: var(--purple);
}

.stat-change {
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}

.stat-change.positive {
    background: var(--green-pale);
    color: var(--green-dark);
}

.stat-change.urgent {
    background: var(--orange-light);
    color: var(--orange);
}

.stat-change.neutral {
    background: var(--purple-light);
    color: var(--purple);
}

.stat-value {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
    margin-bottom: 14px;
}

.stat-bar {
    height: 4px;
    background: var(--green-bg);
    border-radius: 4px;
    overflow: hidden;
}

.stat-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: var(--green-light);
    transition: width 1s ease;
}

.stat-bar-fill.amber {
    background: var(--amber);
}

.stat-bar-fill.orange {
    background: var(--orange);
}

.stat-bar-fill.purple {
    background: var(--purple);
}

/* ===== CHARTS ===== */
.charts-row {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 12px;
    margin-bottom: 14px;
}

.chart-card {
    padding: 20px 24px;
}

.chart-card canvas {
    margin-top: 12px;
}

.chart-filters {
    display: flex;
    gap: 7px;
}

.filter-btn {
    background: none;
    border: 1.5px solid var(--border);
    color: var(--text-muted);
    padding: 6px 14px;
    border-radius: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--green-dark);
    color: #fff;
    border-color: var(--green-dark);
}

.donut-legend {
    margin-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--text-mid);
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-dot.green {
    background: var(--green-dark);
}

.legend-dot.amber {
    background: var(--amber);
}

.legend-dot.orange {
    background: var(--orange);
}

.legend-dot.purple {
    background: var(--purple);
}

/* ===== BOTTOM ROW ===== */
.bottom-row {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 16px;
}

.approvals-card,
.promos-card {
    overflow: visible;
}

/* ===== TABLE ===== */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.data-table thead tr {
    border-bottom: 2px solid var(--green-bg);
}

.data-table thead th {
    padding: 12px 26px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-muted);
    white-space: nowrap;
}

.data-table tbody tr {
    border-bottom: 1px solid var(--green-bg);
    transition: background var(--transition);
}

.data-table tbody tr:hover {
    background: var(--green-bg);
}

.data-table tbody tr:last-child {
    border-bottom: none;
}

.data-table tbody td {
    padding: 16px 26px;
    vertical-align: middle;
    color: var(--text-mid);
    line-height: 1.5;
}

.data-table tbody td strong {
    color: var(--text-dark);
}

.data-table tbody td small {
    font-size: 12px;
    color: var(--text-muted);
}

.txn-id {
    font-family: monospace;
    font-size: 12px;
    color: var(--text-muted);
}

.price-cell strong {
    color: var(--green-dark);
    font-size: 14px;
}

.action-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
}

/* STATUS BADGES */
.status-badge {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.status-badge.pending {
    background: #FFF3E0;
    color: var(--amber);
}

.status-badge.approved {
    background: var(--green-pale);
    color: var(--green-dark);
}

.status-badge.rejected {
    background: #FEE2E2;
    color: var(--red);
}

.status-badge.maintenance {
    background: #FFF3E0;
    color: var(--amber);
}

/* ===== PROMOS ===== */
.promo-list {
    padding: 0 22px 22px;
}

.promo-item {
    border-radius: 11px;
    padding: 16px;
    margin-bottom: 12px;
}

.promo-item.green-bg {
    background: var(--green-pale);
}

.promo-item.blue-bg {
    background: var(--blue-light);
}

.promo-item.amber-bg {
    background: var(--amber-light);
}

.promo-badge {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 5px;
}

.promo-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-dark);
}

.promo-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 3px;
}

/* ===== TABS ===== */
.tab-bar {
    display: flex;
    gap: 4px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 7px;
    margin-bottom: 18px;
    box-shadow: var(--shadow);
}

.tab {
    padding: 10px 22px;
    border-radius: 9px;
    border: none;
    background: none;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--transition);
    display: flex;
    align-items: center;
    gap: 9px;
}

.tab.active {
    background: var(--green-dark);
    color: #fff;
    font-weight: 600;
}

.tab:hover:not(.active) {
    background: var(--green-bg);
    color: var(--text-dark);
}

.tab-count {
    background: var(--orange);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* PROMOS GRID */
.promos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr) 1fr;
    gap: 16px;
    padding: 0 24px 24px;
}

.promo-card {
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 18px;
    background: var(--white);
    transition: all var(--transition);
}

.promo-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.promo-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.promo-card h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 7px;
}

.promo-card p {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.55;
}

.promo-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    font-size: 12px;
    color: var(--text-muted);
}

.discount-tag {
    background: var(--orange-light);
    color: var(--orange);
    font-size: 13px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 7px;
}

.discount-tag.green {
    background: var(--green-pale);
    color: var(--green-dark);
}

.add-promo-card {
    border: 1.5px dashed var(--border) !important;
    background: var(--green-bg) !important;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.add-promo-inner {
    text-align: center;
    color: var(--text-muted);
    font-size: 13px;
}

.add-icon {
    display: block;
    font-size: 18px;
    margin-bottom: 10px;
    opacity: 0.4;
}

/* ===== FEEDBACK ===== */
.feedback-top {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 16px;
}

.rating-card {
    padding: 36px;
    text-align: center;
}

.rating-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--text-muted);
}

.rating-big {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 73px;
    font-weight: 700;
    color: var(--green-dark);
    line-height: 1;
    margin: 10px 0;
}

.stars {
    font-size: 14px;
    color: #F59E0B;
    margin-bottom: 10px;
}

.rating-sub {
    font-size: 13px;
    color: var(--text-muted);
}

.satisfaction-card {
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.satisfaction-card h3 {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 6px;
}

.bar-row {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13px;
}

.bar-row>span:first-child {
    width: 46px;
    color: var(--text-muted);
    flex-shrink: 0;
    text-align: right;
}

.bar-row>span:last-child {
    width: 40px;
    color: var(--text-muted);
    flex-shrink: 0;
}

.bar-track {
    flex: 1;
    height: 10px;
    background: var(--green-bg);
    border-radius: 5px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    border-radius: 5px;
}

.bar-fill.green {
    background: var(--green-light);
}

.bar-fill.green-light {
    background: #7CC86E;
}

.bar-fill.amber-fill {
    background: var(--amber);
}

.bar-fill.orange-fill {
    background: var(--orange);
}

.bar-fill.red-fill {
    background: var(--red);
}

.review-filters {
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 16px 24px;
    flex-wrap: wrap;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 16px;
}

.review-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 28px;
    box-shadow: var(--shadow);
}

.review-top {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.reviewer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--amber-light);
    color: var(--amber);
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.reviewer-avatar.green-avatar {
    background: var(--green-pale);
    color: var(--green-dark);
}

.reviewer-name {
    font-weight: 700;
    font-size: 13px;
    color: var(--text-dark);
}

.verified-badge {
    background: var(--green-pale);
    color: var(--green-dark);
    font-size: 17px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 20px;
    margin-left: 8px;
}

.review-stars {
    font-size: 14px;
    color: #F59E0B;
    margin-top: 4px;
}

.review-stars span {
    font-size: 12px;
    color: var(--text-muted);
    margin-left: 8px;
}

.review-text {
    font-size: 14px;
    color: var(--text-mid);
    line-height: 1.7;
    font-style: italic;
}

/* ===== MEDIA GALLERY ===== */
.media-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}

.media-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: all var(--transition);
}

.media-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.media-img-wrap {
    position: relative;
}

.media-img {
    height: 160px;
    background: var(--green-bg);
    width: 100%;
}

.media-status {
    position: absolute;
    top: 12px;
    left: 12px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.5px;
    padding: 4px 12px;
    border-radius: 20px;
}

.media-status.live {
    background: var(--green-dark);
    color: #fff;
}

.media-status.draft {
    background: var(--amber);
    color: #fff;
}

.media-info {
    padding: 16px;
}

.media-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin-bottom: 8px;
}

.media-id {
    font-size: 12px;
    color: var(--text-muted);
}

.media-meta {
    font-size: 12px;
    color: var(--text-muted);
    line-height: 2;
}

.add-media-card {
    border: 1.5px dashed var(--border) !important;
    background: var(--green-bg) !important;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 260px;
}

.add-media-inner {
    text-align: center;
    color: var(--text-muted);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

/* ===== ATTRACTIONS ===== */
.filters-row {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 18px 26px;
    border-bottom: 1px solid var(--green-bg);
    flex-wrap: wrap;
}

.filter-select {
    padding: 9px 16px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    color: var(--text-mid);
    background: var(--white);
    cursor: pointer;
    outline: none;
    transition: border-color var(--transition);
}

.filter-select:focus {
    border-color: var(--green-light);
}

.search-input {
    padding: 9px 16px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    color: var(--text-mid);
    outline: none;
    transition: border-color var(--transition);
}

.search-input:focus {
    border-color: var(--green-light);
}

.exhibit-thumb {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

/* ===== ANNOUNCEMENTS ===== */
.announce-list {
    padding: 18px 26px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.announce-item {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    padding: 18px;
    background: var(--green-bg);
    border-radius: 12px;
}

.announce-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

.announce-icon.orange {
    background: var(--orange-light);
}

.announce-icon.green {
    background: var(--green-pale);
}

.announce-body {
    flex: 1;
}

.announce-body strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 5px;
}

.announce-body p {
    font-size: 13px;
    color: var(--text-mid);
    margin-bottom: 7px;
    line-height: 1.6;
}

.announce-body small {
    font-size: 12px;
    color: var(--text-muted);
}

/* ===== SETTINGS ===== */
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.settings-section {
    padding: 18px;
}

.settings-section h3 {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 22px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-input {
    width: 100%;
    padding: 11px 16px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
    font-size: 13px;
    color: var(--text-dark);
    background: var(--white);
    outline: none;
    transition: border-color var(--transition);
}

.form-input:focus {
    border-color: var(--green-light);
}

textarea.form-input {
    resize: vertical;
}

.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid var(--green-bg);
    font-size: 13px;
    color: var(--text-mid);
}

.toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--border);
    border-radius: 24px;
    cursor: pointer;
    transition: background var(--transition);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: transform var(--transition);
}

.toggle input:checked+.toggle-slider {
    background: var(--green-light);
}

.toggle input:checked+.toggle-slider::before {
    transform: translateX(20px);
}

.form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 6px;
}

/* ===== PROFILE ===== */
.profile-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 14px;
}

.profile-card {
    padding: 36px;
    text-align: center;
}

.profile-avatar-big {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: var(--green-dark);
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
}

.profile-card h2 {
    font-size: 12px;
    font-weight: 700;
}

.profile-card p {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 5px;
}

/* ===== PAGINATION ===== */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 26px;
    font-size: 13px;
    color: var(--text-muted);
    border-top: 1px solid var(--green-bg);
    gap: 12px;
}

.pagination>div {
    display: flex;
    gap: 8px;
}

/* ===== MODALS ===== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 300;
    display: none;
    align-items: center;
    justify-content: center;
}

.modal-overlay.open {
    display: flex;
}

.modal {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    margin: 16px;
    animation: modalIn 0.2s ease;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
}

.modal-header h3 {
    font-size: 15px;
    font-weight: 700;
}

.modal-header button {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    border: none;
    background: var(--green-bg);
    cursor: pointer;
    font-size: 14px;
    color: var(--text-muted);
    transition: all var(--transition);
}

.modal-header button:hover {
    background: var(--border);
    color: var(--text-dark);
}

.modal-body {
    padding: 18px;
}

/* UPLOAD ZONE */
.upload-drop-zone {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: var(--text-muted);
}

.upload-drop-zone:hover {
    border-color: var(--green-light);
    background: var(--green-pale);
}

.upload-drop-zone p {
    font-size: 13px;
    font-weight: 600;
}

.upload-drop-zone small {
    font-size: 12px;
}

/* ===== TOAST ===== */
.toast {
    position: fixed;
    bottom: 28px;
    right: 28px;
    background: var(--green-dark);
    color: white;
    padding: 16px 26px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: var(--shadow-lg);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 500;
    pointer-events: none;
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-row {
        grid-template-columns: 1fr;
    }

    .bottom-row {
        grid-template-columns: 1fr;
    }

    .promos-grid {
        grid-template-columns: 1fr 1fr;
    }

    .media-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 900px) {
    :root {
        --sidebar-w: 0px;
    }

    .sidebar {
        transform: translateX(-258px);
    }

    .sidebar.open {
        transform: translateX(0);
        width: 258px;
    }

    .main-content {
        margin-left: 0;
    }

    .settings-grid {
        grid-template-columns: 1fr;
    }

    .profile-grid {
        grid-template-columns: 1fr;
    }

    .feedback-top {
        grid-template-columns: 1fr;
    }

    .promos-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .page {
        padding: 16px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .media-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
  </style>

</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">
      <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 4C9.4 4 4 9.4 4 16s5.4 12 12 12 12-5.4 12-12S22.6 4 16 4z" fill="currentColor" opacity="0.2"/>
        <path d="M10 14c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v6c0 1.1-.9 2-2 2h-8c-1.1 0-2-.9-2-2v-6z" fill="currentColor"/>
        <circle cx="13" cy="10" r="2" fill="currentColor"/>
        <circle cx="19" cy="10" r="2" fill="currentColor"/>
        <circle cx="13" cy="17" r="1" fill="white"/>
        <circle cx="19" cy="17" r="1" fill="white"/>
      </svg>
    </div>
    <div class="brand-text">
      <span class="brand-name">WildTrack</span>
      <span class="brand-sub">Admin Portal</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="#" class="nav-item active" data-page="overview">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      </span>
      <span class="nav-label">Overview</span>
    </a>
    <a href="#" class="nav-item" data-page="ticketing">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg>
      </span>
      <span class="nav-label">Ticketing</span>
      <span class="nav-badge">14</span>
    </a>
    <a href="#" class="nav-item" data-page="feedback">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      </span>
      <span class="nav-label">Feedback & Reviews</span>
    </a>
    <a href="#" class="nav-item" data-page="media">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
      </span>
      <span class="nav-label">Media Gallery</span>
    </a>
    <a href="#" class="nav-item" data-page="events">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </span>
      <span class="nav-label">Events</span>
    </a>
    <a href="#" class="nav-item" data-page="mapeditor">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
      </span>
      <span class="nav-label">Zoo Map Editor</span>
    </a>

    <div class="nav-section-label" style="margin-top:16px;">Management</div>
    <a href="#" class="nav-item" data-page="staff">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      </span>
      <span class="nav-label">Staff</span>
    </a>
    <a href="#" class="nav-item" data-page="reports">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </span>
      <span class="nav-label">Reports</span>
    </a>
    <a href="#" class="nav-item" data-page="announcements">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 000 6h20a3 3 0 000-6z"/><path d="M17 11V5a5 5 0 00-10 0v6"/><path d="M12 20v2"/></svg>
      </span>
      <span class="nav-label">Announcements</span>
    </a>
    <a href="#" class="nav-item" data-page="settings">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
      </span>
      <span class="nav-label">Settings</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card" data-page="profile" onclick="showPage('profile')">
      <div class="user-avatar" id="sidebarAvatar">AR</div>
      <div class="user-info">
        <span class="user-name" id="sidebarUsername">Admin Ranger</span>
        <span class="user-role">System Administrator</span>
      </div>
    </div>
    <a href="javascript:void(0)" class="logout-btn" title="Logout" onclick="doLogout()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
      </svg>
    </a>
  </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content" id="mainContent">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" onclick="toggleSidebar()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="page-breadcrumb">
        <span id="pageTitle">Overview</span>
        <span class="live-badge">● LIVE</span>
      </div>
    </div>
    <div class="topbar-right">
      <div class="search-bar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="globalSearchInput" placeholder="Search anything..." oninput="handleGlobalSearch(this.value)" autocomplete="off"/>
      </div>
      <!-- Global search results dropdown -->
      <div id="globalSearchResults" style="
        display:none; position:absolute; top:54px; right:240px;
        width:340px; background:var(--white); border:1px solid var(--border);
        border-radius:var(--radius); box-shadow:var(--shadow-lg);
        z-index:300; overflow:hidden; max-height:400px; overflow-y:auto;">
      </div>
      <button class="icon-btn notif-btn" onclick="toggleNotifications(event)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        <span class="notif-dot"></span>
      </button>
      <!-- Notification Panel -->
      <div class="notif-panel" id="notifPanel">
        <div class="notif-header">
          <span>Notifications</span>
          <button onclick="markAllRead()">Mark all read</button>
        </div>
        <div class="notif-list" id="notifList">
          <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;">Loading…</div>
        </div>
      </div>
    </div>
  </header>

  <!-- PAGE: OVERVIEW -->
  <section class="page active" id="page-overview">
    <div class="page-header">
      <div>
        <h1>Zoo Dashboard Overview</h1>
        <p id="overviewGreeting">Loading…</p>
      </div>
      <button class="btn btn-primary" onclick="exportOverviewReport()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export Report
      </button>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <span class="stat-change positive">All Time</span>
        </div>
        <div class="stat-value" id="statTotalTickets">—</div>
        <div class="stat-label">Total Tickets Sold</div>
        <div class="stat-bar"><div class="stat-bar-fill" id="statTotalTicketsBar" style="width:0%"></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          </div>
          <span class="stat-change positive">All Time</span>
        </div>
        <div class="stat-value" id="statTotalRevenue">—</div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-bar"><div class="stat-bar-fill amber" style="width:70%"></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
          </div>
          <span class="stat-change urgent" id="statPendingUrgent">Urgent</span>
        </div>
        <div class="stat-value" id="statPendingCount">—</div>
        <div class="stat-label">Pending Approvals</div>
        <div class="stat-bar"><div class="stat-bar-fill orange" id="statPendingBar" style="width:0%"></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg>
          </div>
          <span class="stat-change neutral">Today</span>
        </div>
        <div class="stat-value" id="statTodayTickets">—</div>
        <div class="stat-label">Today's Tickets</div>
        <div class="stat-bar"><div class="stat-bar-fill purple" id="statTodayBar" style="width:0%"></div></div>
      </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="charts-row">
      <div class="card chart-card large">
        <div class="card-header">
          <h3>Visitor Trends</h3>
          <div class="chart-filters">
            <button class="filter-btn active" onclick="setVisitorFilter(this,7)">7D</button>
            <button class="filter-btn" onclick="setVisitorFilter(this,30)">30D</button>
            <button class="filter-btn" onclick="setVisitorFilter(this,90)">90D</button>
          </div>
        </div>
        <canvas id="visitorChart" height="200"></canvas>
      </div>
      <div class="card chart-card small">
        <div class="card-header">
          <h3>Ticket Types</h3>
        </div>
        <canvas id="ticketChart" height="200"></canvas>
        <div class="donut-legend" id="donutLegend">
          <div class="legend-item"><span class="legend-dot green"></span>Loading…</div>
        </div>
      </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-row">
      <!-- Pending Approvals -->
      <div class="card approvals-card">
        <div class="card-header">
          <h3>Pending Ticket Approvals</h3>
          <a href="#" class="card-link" onclick="showPage('ticketing')">View All →</a>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>TXN ID</th>
              <th>Customer</th>
              <th>Tickets</th>
              <th>Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="overviewApprovalBody">
            <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">Loading…</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Active Promotions Summary -->
      <div class="card promos-card">
        <div class="card-header">
          <h3>Active Promotions</h3>
          <a href="#" class="card-link" onclick="goToPromotions()">Manage →</a>
        </div>
        <div class="promo-list" id="overviewPromoList">
          <div class="promo-item green-bg">
            <div class="promo-badge" style="color:var(--text-muted);">Loading…</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PAGE: TICKETING -->
  <section class="page" id="page-ticketing">
    <div class="page-header">
      <div>
        <h1>Ticketing Management</h1>
        <p>Approve payments, manage pricing & promotions</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tab-bar">
      <button class="tab active" onclick="switchTab(this,'tab-approvals')">Payment Approvals <span class="tab-count" id="tabPendingCount">…</span></button>
      <button class="tab" onclick="switchTab(this,'tab-pricing')">Pricing</button>
      <button class="tab" onclick="switchTab(this,'tab-promotions')">Promotions</button>
      <button class="tab" onclick="switchTab(this,'tab-payment-settings')">Payment Settings</button>
    </div>

    <!-- Tab: Approvals -->
    <div class="tab-content active" id="tab-approvals">
      <!-- Sub-tab bar: Recent vs History -->
      <div style="display:flex;gap:8px;margin-bottom:14px;align-items:center;flex-wrap:wrap;">
        <button class="btn btn-primary" id="approvalSubRecent"   onclick="switchApprovalView('recent')"  style="font-size:12px;padding:7px 16px;">Recent (3 days)</button>
        <button class="btn btn-outline" id="approvalSubHistory"  onclick="switchApprovalView('history')" style="font-size:12px;padding:7px 16px;">History</button>
        <span style="margin-left:auto;font-size:12px;color:var(--text-muted);" id="approvalViewLabel">Showing pending + last 3 days of approved/rejected</span>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 id="approvalCardTitle">Pending &amp; Recent Payments</h3>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="text" class="search-input" id="approvalSearch"
                   placeholder="Search TXN or customer…"
                   oninput="filterApprovalTable()"
                   style="min-width:200px;" />
            <select class="filter-select" id="approvalStatusFilter" onchange="filterApprovalTable()">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>TXN ID</th><th>Customer</th><th>Tickets</th><th>Amount</th><th>Payment Proof</th><th>Date</th><th>Status</th><th>Approved By</th><th>Actions</th></tr>
          </thead>
          <tbody id="approvalTableBody">
            <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--text-muted);">Loading payments…</td></tr>
          </tbody>
        </table>
        <div class="pagination" id="approvalPagination" style="display:none;">
          <span id="approvalPaginationInfo" style="color:var(--text-muted);font-size:13px;"></span>
        </div>
      </div>
    </div>

    <!-- Tab: Pricing -->
    <div class="tab-content" id="tab-pricing">
      <!-- Ticket Prices -->
      <div class="card">
        <div class="card-header">
          <h3>Ticket Categories &amp; Pricing</h3>
          <p style="font-size:13px;color:var(--text-muted)">Changes take effect immediately on the visitor booking page</p>
        </div>
        <table class="data-table">
          <thead><tr><th>Category</th><th>Description</th><th>Age Range</th><th>Current Price</th><th>Actions</th></tr></thead>
          <tbody id="pricingTableBody">
            <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">Loading prices&#8230;</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Add-on Prices (NEW SECTION) -->
      <div class="card" style="margin-top:16px;">
        <div class="card-header">
          <h3>Add-on &amp; Enhance Visit Pricing</h3>
          <p style="font-size:13px;color:var(--text-muted)">Changes take effect immediately on the visitor booking page</p>
        </div>
        <table class="data-table">
          <thead><tr><th>Add-on</th><th>Description</th><th>Current Price</th><th>Actions</th></tr></thead>
          <tbody id="addonPricingTableBody">
            <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted);">Loading add-on prices&#8230;</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Animal Feeding Prices -->
      <div class="card" style="margin-top:16px;">
        <div class="card-header">
          <div>
            <h3>🐾 Animal Feeding Session Prices</h3>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px;">Feed cup prices shown on the Animal Feeding page (weekends &amp; public holidays)</p>
          </div>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>Animal</th><th>Session Time</th><th>Price / Cup</th><th>Actions</th></tr>
          </thead>
          <tbody id="feedingPricingTableBody">
            <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted);">Loading feeding prices&#8230;</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="tab-content" id="tab-promotions">
      <div class="card">
        <div class="card-header">
          <h3>Voucher Promotions</h3>
          <button class="btn btn-primary" onclick="openPromoModal()">+ Add Voucher</button>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>Code</th><th>Type</th><th>Discount</th><th>Min Spend</th><th>Uses</th><th>Expires</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody id="vouchersTableBody">
            <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Loading vouchers…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New tab: Payment Settings -->

<div class="tab-content" id="tab-payment-settings">
    <div class="card">
        <div class="card-header">
            <h3>Touch 'n Go eWallet Settings</h3>
            <p style="font-size:13px;color:var(--text-muted);">Changes affect the QR code shown to visitors during checkout.</p>
        </div>

        <div style="padding:0 24px 24px;">
            <!-- Current QR preview -->
            <div style="margin-bottom:24px;text-align:center;">
                <label class="form-label">Current QR Code</label>
                <div style="display:inline-block;border:1px solid var(--border);border-radius:12px;padding:12px;background:#fff;">
                    <img id="currentTngQr" src="" alt="TNG QR" style="width:180px;height:180px;object-fit:contain;">
                </div>
                <p id="currentReceiverName" style="margin-top:8px;font-size:13px;color:var(--text-muted);">Loading…</p>
            </div>

            <!-- Update form -->
            <div class="form-group">
                <label>Receiver Name (shown on payment page)</label>
                <input type="text" id="newReceiverName" class="form-input" placeholder="WildTrack Safari Park">
            </div>

            <div class="form-group">
                <label>Upload New QR Code Image</label>
                <div class="upload-drop-zone" id="qrUploadZone" style="cursor:pointer;padding:24px;text-align:center;"
                     onclick="document.getElementById('qrFileInput').click()">
                    <span style="font-size:28px;">📁</span>
                    <p>Click to choose a new QR image</p>
                    <small>PNG, JPG, WebP · Max 2MB</small>
                </div>
                <input type="file" id="qrFileInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                <div id="qrPreviewNew" style="display:none;margin-top:12px;text-align:center;">
                    <img id="newQrPreview" style="max-height:120px;border-radius:8px;">
                    <button class="btn-edit" style="margin-left:8px;" onclick="clearNewQr()">Remove</button>
                </div>
            </div>

            <button class="btn btn-primary" onclick="openPasswordModalForTng()" style="margin-top:8px;">Save Payment Settings</button>
            <!-- QR Change History -->
          <div style="margin-top:32px;">
              <hr style="border-top:1px solid var(--border); margin:16px 0;">
              <div style="display:flex; align-items:center; justify-content:space-between;">
                 <h3 style="font-size:14px; font-weight:700;">📜 QR Change History</h3>
                  <button class="btn-edit" onclick="refreshTngHistory()" style="padding:4px 12px;">⟳ Refresh</button>
              </div>
              <div id="tngHistoryList" style="margin-top:12px; font-size:13px;">
                  <div style="color:var(--text-muted); text-align:center; padding:20px;">Loading history…</div>
              </div>
          </div>
        </div>
    </div>
</div>
  </section>

  <!-- PAGE: FEEDBACK -->
  <section class="page" id="page-feedback">
    <div class="page-header">
      <div>
        <h1>Visitor Feedback &amp; Reviews</h1>
        <p>Real-time guest feedback submitted via the Get in Touch page</p>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" onclick="loadFeedback()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Stats row -->
    <div class="feedback-top">
      <div class="card rating-card">
        <div class="rating-label">AVERAGE RATING</div>
        <div class="rating-big" id="fbAvgRating">—</div>
        <div class="stars" id="fbAvgStars">☆☆☆☆☆</div>
        <div class="rating-sub" id="fbTotalLabel">Loading…</div>
      </div>
      <div class="card satisfaction-card">
        <h3>Satisfaction Breakdown</h3>
        <div class="bar-row"><span>5 ★</span><div class="bar-track"><div class="bar-fill green"       id="fbBar5" style="width:0%"></div></div><span id="fbPct5">0%</span></div>
        <div class="bar-row"><span>4 ★</span><div class="bar-track"><div class="bar-fill green-light"  id="fbBar4" style="width:0%"></div></div><span id="fbPct4">0%</span></div>
        <div class="bar-row"><span>3 ★</span><div class="bar-track"><div class="bar-fill amber-fill"  id="fbBar3" style="width:0%"></div></div><span id="fbPct3">0%</span></div>
        <div class="bar-row"><span>2 ★</span><div class="bar-track"><div class="bar-fill orange-fill" id="fbBar2" style="width:0%"></div></div><span id="fbPct2">0%</span></div>
        <div class="bar-row"><span>1 ★</span><div class="bar-track"><div class="bar-fill red-fill"    id="fbBar1" style="width:0%"></div></div><span id="fbPct1">0%</span></div>
      </div>
      <!-- Summary chips -->
      <div class="card" style="min-width:160px;display:flex;flex-direction:column;gap:14px;justify-content:center;padding:22px 26px;">
        <div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:4px;">Unread</div>
          <div style="font-size:28px;font-weight:700;color:var(--orange);" id="fbUnreadCount">—</div>
        </div>
        <div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:4px;">Awaiting Reply</div>
          <div style="font-size:28px;font-weight:700;color:var(--green-dark);" id="fbPendingCount">—</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-top:16px;">
      <div class="review-filters" style="display:flex;gap:10px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid var(--border);">
        <div class="search-bar" style="flex:1;min-width:200px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="fbSearch" placeholder="Search name, email, message…" oninput="debounceFbSearch()" />
        </div>
        <select class="filter-select" id="fbRatingFilter" onchange="loadFeedback()">
          <option value="">All Ratings</option>
          <option value="5">5 ★ Excellent</option>
          <option value="4">4 ★ Good</option>
          <option value="3">3 ★ Okay</option>
          <option value="2">2 ★ Poor</option>
          <option value="1">1 ★ Very Poor</option>
        </select>
        <select class="filter-select" id="fbStatusFilter" onchange="loadFeedback()">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="replied">Replied</option>
          <option value="flagged">Flagged</option>
        </select>
      </div>

      <!-- Reviews list -->
      <div class="reviews-list" id="fbList">
        <div style="text-align:center;padding:40px;color:var(--text-muted);">Loading feedback…</div>
      </div>

      <!-- Pagination -->
      <div class="pagination" id="fbPagination" style="display:none;">
        <span id="fbPaginationInfo" style="color:var(--text-muted);font-size:13px;"></span>
        <div>
          <button class="btn-page" id="fbPrevBtn" onclick="changeFbPage(-1)">← Prev</button>
          <button class="btn-page" id="fbNextBtn" onclick="changeFbPage(1)">Next →</button>
        </div>
      </div>
    </div>

    <!-- Contact Info section -->
    <div style="margin-top:24px;">
      <div class="page-header" style="margin-bottom:12px;">
        <div>
          <h1>Contact Info Cards</h1>
          <p>These cards appear on the visitor "Get in Touch" page</p>
        </div>
        <button class="btn btn-primary" onclick="openContactModal()">+ Add Contact Card</button>
      </div>
      <div class="card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Icon</th>
              <th>Department</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="contactInfoBody">
            <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--text-muted);">Loading…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Reply Modal -->
  <div class="modal-overlay" id="fbReplyModal">
    <div class="modal" style="max-width:540px;">
      <div class="modal-header">
        <h3>↩ Reply to Feedback</h3>
        <button onclick="closeModal('fbReplyModal')">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="fbReplyId" />
        <!-- Original message preview -->
        <div style="background:var(--green-bg);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:16px;" id="fbOriginalPreview"></div>
        <div class="form-group">
          <label>Your Reply <span style="color:var(--red)">*</span></label>
          <textarea class="form-input" id="fbReplyText" rows="5" placeholder="Type your reply to the visitor…" style="resize:vertical;min-height:110px;"></textarea>
        </div>
        <p style="font-size:12px;color:var(--text-muted);">
          💡 If the visitor has an account, they will receive an in-app notification with your reply.
        </p>
      </div>
      <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 20px;border-top:1px solid var(--border);">
        <button class="btn btn-outline" onclick="closeModal('fbReplyModal')">Cancel</button>
        <button class="btn btn-primary" onclick="submitFbReply()">Send Reply</button>
      </div>
    </div>
  </div>

  <!-- Contact Info Modal -->
  <div class="modal-overlay" id="contactModal">
    <div class="modal" style="max-width:460px;">
      <div class="modal-header">
        <h3 id="contactModalTitle">Add Contact Card</h3>
        <button onclick="closeModal('contactModal')">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cmId" value=""/>
        <div style="display:grid;grid-template-columns:80px 1fr;gap:12px;">
          <div class="form-group">
            <label>Icon</label>
            <input type="text" class="form-input" id="cmIcon" placeholder="🦁" maxlength="4"/>
          </div>
          <div class="form-group">
            <label>Department / Name <span style="color:var(--red)">*</span></label>
            <input type="text" class="form-input" id="cmDept" placeholder="e.g. Ranger Program"/>
          </div>
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" class="form-input" id="cmPhone" placeholder="+6012-345 6789"/>
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" class="form-input" id="cmEmail" placeholder="dept@wildtrackzoo.my"/>
        </div>
        <div class="form-group">
          <label>Sort Order <small style="color:var(--text-muted);font-weight:400;">(lower = first)</small></label>
          <input type="number" class="form-input" id="cmSort" value="0" min="0" max="99"/>
        </div>
      </div>
      <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 20px;border-top:1px solid var(--border);">
        <button class="btn btn-outline" onclick="closeModal('contactModal')">Cancel</button>
        <button class="btn btn-primary" onclick="submitContactModal()"><span id="cmBtnText">Create Card</span></button>
      </div>
    </div>
  </div>

  <!-- PAGE: MEDIA GALLERY -->
  <section class="page" id="page-media">
    <div class="page-header">
      <div>
        <h1>Media Gallery — Slider Images</h1>
        <p>Images marked <strong>LIVE</strong> appear in the visitor homepage slider.</p>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" onclick="loadMediaGallery()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
          Refresh
        </button>
        <button class="btn btn-primary" onclick="openUploadModal()">+ Upload New Image</button>
      </div>
    </div>

    <div style="background:#f0f7ef;border:1px solid #c3dbbe;border-radius:12px;padding:14px 20px;margin-bottom:18px;display:flex;align-items:center;gap:12px;font-size:13px;color:#2a5a2e;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
      <span>Click <strong>Set Live</strong> / <strong>Set Draft</strong> on any card to instantly show or hide it on the visitor homepage slider.</span>
    </div>

    <div class="media-grid" id="mediaGrid">
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">Loading images…</div>
    </div>

    <div class="pagination" id="mediaPagination" style="display:none;">
      <span id="mediaCount"></span>
    </div>
  </section>

  <!-- PAGE: EVENTS MANAGEMENT -->
  <section class="page" id="page-events">
    <div class="page-header">
      <div>
        <h1>Events &amp; Talk Times Management</h1>
        <p>Add, edit, or remove animal talks. Changes appear live on the visitor schedule page.</p>
      </div>
      <button class="btn btn-primary" onclick="openEventModal()">+ Add New Event</button>
    </div>

    <!-- Summary chips -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
      <div style="background:var(--green-pale);border:1px solid var(--border);border-radius:10px;padding:10px 18px;font-size:13px;">
        <strong id="evtCountTotal">–</strong> <span style="color:var(--text-muted);">Total Events</span>
      </div>
      <div style="background:var(--green-pale);border:1px solid var(--border);border-radius:10px;padding:10px 18px;font-size:13px;">
        <strong id="evtCountActive">–</strong> <span style="color:var(--text-muted);">Active</span>
      </div>
      <div style="background:var(--amber-light);border:1px solid #f5d79e;border-radius:10px;padding:10px 18px;font-size:13px;">
        <strong id="evtCountSpecific">–</strong> <span style="color:var(--text-muted);">Specific-Date</span>
      </div>
    </div>

    <div class="card">
      <!-- Filters -->
      <div class="filters-row">
        <div class="search-bar" style="flex:1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="evtSearch" placeholder="Search by name or venue…" oninput="filterEventsTable()" />
        </div>
        <select class="filter-select" id="evtSessionFilter" onchange="filterEventsTable()">
          <option value="">All Sessions</option>
          <option value="morning">Morning</option>
          <option value="afternoon">Afternoon</option>
        </select>
        <select class="filter-select" id="evtStatusFilter" onchange="filterEventsTable()">
          <option value="">All Statuses</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <table class="data-table" id="eventsTable">
        <thead>
          <tr>
            <th>Event Name</th>
            <th>Session</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="eventsTableBody">
          <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">Loading events…</td></tr>
        </tbody>
      </table>

      <div class="pagination" id="evtPagination" style="display:none;">
        <span id="evtPaginationInfo"></span>
      </div>
    </div>
  </section>

  <!-- PAGE: STAFF -->
  <section class="page" id="page-staff">
    <div class="page-header">
      <div><h1>Staff Management</h1><p>Manage zoo staff accounts and roles</p></div>
      <button class="btn btn-primary" onclick="openAddStaffModal()">+ Add Staff Member</button>
    </div>
    <div class="card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Staff</th>
            <th>Role</th>
            <th>Position</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="staffTableBody">
          <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- PAGE: REPORTS -->
  <section class="page" id="page-reports">
    <div class="page-header">
      <div><h1>Reports & Analytics</h1><p>Detailed insights on performance, revenue and visitors</p></div>
      <button class="btn btn-primary" onclick="exportReportsPDF()">Export PDF</button>
    </div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-top"><div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div></div><div class="stat-value" id="reportMonthVisitors">—</div><div class="stat-label">Monthly Visitors</div></div>
      <div class="stat-card"><div class="stat-top"><div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div></div><div class="stat-value" id="reportMonthRevenue">—</div><div class="stat-label">Monthly Revenue</div></div>
      <div class="stat-card"><div class="stat-top"><div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg></div></div><div class="stat-value" id="reportSatisfactionRate">—</div><div class="stat-label">Satisfaction Rate</div></div>
      <div class="stat-card"><div class="stat-top"><div class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h6"/></svg></div></div><div class="stat-value" id="reportMonthTickets">—</div><div class="stat-label">Tickets Sold</div></div>
    </div>
    <div class="card" style="margin-top:20px;"><div class="card-header"><h3>Monthly Revenue Trend</h3></div><canvas id="revenueChart" height="120"></canvas></div>
  </section>

  <!-- PAGE: ANNOUNCEMENTS -->
  <section class="page" id="page-announcements">
    <div class="page-header">
      <div><h1>Announcements</h1><p>Publish notices that appear live on the visitor page</p></div>
      <button class="btn btn-primary" onclick="openNewAnnouncementModal()">+ New Announcement</button>
    </div>
    <div class="card">
      <div class="announce-list" id="announceList">
        <div style="text-align:center;padding:32px;color:var(--text-muted);">Loading…</div>
      </div>
    </div>
  </section>

  <!-- Add / Edit Staff Modal -->
  <div class="modal-overlay" id="staffModal">
    <div class="modal" style="max-width:480px;">
      <div class="modal-header">
        <h3 id="staffModalTitle">Add Staff Member</h3>
        <button onclick="closeModal('staffModal')">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="smUserId" value=""/>
 
        <div class="form-group">
          <label>Full Name <span style="color:var(--red)">*</span></label>
          <input type="text" class="form-input" id="smName" placeholder="e.g. Ahmad Faris"/>
        </div>
 
        <div class="form-group">
          <label>Email Address <span style="color:var(--red)">*</span></label>
          <input type="email" class="form-input" id="smEmail" placeholder="staff@wildtrack.com"/>
        </div>
 
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label>Role <span style="color:var(--red)">*</span></label>
            <select class="form-input" id="smRole">
              <option value="worker">Worker</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label>Position / Department</label>
            <input type="text" class="form-input" id="smPosition" placeholder="e.g. Zoo Keeper"/>
          </div>
        </div>
 
        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" class="form-input" id="smPhone" placeholder="+60 12-345 6789"/>
        </div>
 
        <!-- Only shown when creating, hidden when editing -->
        <div class="form-group" id="smPwGroup">
          <label>Temporary Password <span style="color:var(--red)">*</span></label>
          <input type="text" class="form-input" id="smTempPw" placeholder="Min. 6 characters"/>
          <small style="color:var(--text-muted);margin-top:4px;display:block;">
            Staff will be required to change this on their first login.
          </small>
        </div>
 
      </div>
      <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 20px;border-top:1px solid var(--border);">
        <button class="btn btn-outline" onclick="closeModal('staffModal')">Cancel</button>
        <button class="btn btn-primary" onclick="submitStaffModal()">
          <span id="smBtnText">Create Account</span>
        </button>
      </div>
    </div>
  </div>
 
  <!-- Reset Password Modal -->
  <div class="modal-overlay" id="resetPwModal">
    <div class="modal" style="max-width:380px;">
      <div class="modal-header">
        <h3>Reset Password</h3>
        <button onclick="closeModal('resetPwModal')">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rpUserId"/>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;">
          Set a new temporary password for <strong id="rpStaffName"></strong>.
          They will be forced to change it on next login.
        </p>
        <div class="form-group">
          <label>New Temporary Password</label>
          <input type="text" class="form-input" id="rpTempPw" placeholder="Min. 6 characters"/>
        </div>
      </div>
      <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 20px;border-top:1px solid var(--border);">
        <button class="btn btn-outline" onclick="closeModal('resetPwModal')">Cancel</button>
        <button class="btn btn-primary" onclick="submitResetPassword()">Reset Password</button>
      </div>
    </div>
  </div>
 

  <!-- PAGE: SETTINGS -->
  <section class="page" id="page-settings">
    <div class="page-header"><div><h1>Settings</h1><p>System configuration and preferences</p></div></div>
    <div class="settings-grid">
 
      <!-- Zoo Opening Hours (dynamic) -->
      <div class="card settings-section">
        <h3>🕘 Opening Hours</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;">
          Changes are reflected immediately on the visitor page.</p>
 
        <div class="form-group">
          <label>Opening Time</label>
          <input type="time" class="form-input" id="settingOpenTime" value="09:00"/>
        </div>
        <div class="form-group">
          <label>Closing Time</label>
          <input type="time" class="form-input" id="settingCloseTime" value="18:00"/>
        </div>
        <div class="form-group">
          <label>Last Entry (minutes before closing)</label>
          <input type="number" class="form-input" id="settingLastEntry" value="60" min="0" max="360" step="5"/>
          <small style="color:var(--text-muted);">e.g. 60 = last entry 1 hour before closing</small>
        </div>
        <div class="form-group">
          <label>Last Online Purchase (minutes before closing)</label>
          <input type="number" class="form-input" id="settingOnlinePurchase" value="180" min="0" max="360" step="5"/>
          <small style="color:var(--text-muted);">e.g. 180 = purchases close 3 hours before closing</small>
        </div>
        <button class="btn btn-primary" style="margin-top:10px;" onclick="saveZooSettings()">
          💾 Save Opening Hours
        </button>
      </div>
 
      <!-- Notification Preferences — dynamic, saved to zoo_settings -->
      <div class="card settings-section">
        <h3>🔔 Notification Preferences</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Choose which alerts you receive in the admin notification bell.</p>
        <div class="toggle-row">
          <span>Pending ticket approval alerts</span>
          <label class="toggle">
            <input type="checkbox" id="notifPrefTickets" onchange="saveNotifPref('notif_pref_tickets', this.checked)"/>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="toggle-row">
          <span>New review / feedback alerts</span>
          <label class="toggle">
            <input type="checkbox" id="notifPrefReviews" onchange="saveNotifPref('notif_pref_reviews', this.checked)"/>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="toggle-row">
          <span>Expired event auto-deactivation alerts</span>
          <label class="toggle">
            <input type="checkbox" id="notifPrefEvents" onchange="saveNotifPref('notif_pref_events', this.checked)"/>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="toggle-row">
          <span>New 5-star review highlights</span>
          <label class="toggle">
            <input type="checkbox" id="notifPrefStars" onchange="saveNotifPref('notif_pref_stars', this.checked)"/>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <p id="notifPrefStatus" style="font-size:12px;color:var(--green-mid);margin-top:12px;min-height:16px;"></p>
      </div>
 
    </div>
  </section>
 

  <!-- PAGE: PROFILE -->
  <section class="page" id="page-profile">
    <div class="page-header"><div><h1>My Profile</h1><p>Manage your account details</p></div></div>
    <div class="profile-grid">
      <div class="card profile-card">
        <div class="profile-avatar-big">AR</div>
        <h2>Admin Ranger</h2>
        <p>System Administrator</p>
        <button class="btn btn-outline" style="margin-top:16px;">Change Avatar</button>
      </div>
      <div class="card" style="flex:1;">
        <h3 style="margin-bottom:20px;">Account Details</h3>
        <div class="form-group"><label>Full Name</label><input type="text" class="form-input" value="Admin Ranger"/></div>
        <div class="form-group"><label>Email</label><input type="email" class="form-input" value="admin@wildtrack.com"/></div>
        <div class="form-group"><label>Phone</label><input type="text" class="form-input" value="+60 12-345 6789"/></div>
        <div class="form-group"><label>Role</label><input type="text" class="form-input" value="System Administrator" readonly/></div>
        <div style="display:flex;gap:10px;margin-top:8px;">
          <button class="btn btn-primary">Save Changes</button>
          <button class="btn btn-outline">Change Password</button>
        </div>
      </div>
    </div>
  </section>

  <!-- PAGE: ZOO MAP EDITOR -->
  <section class="page" id="page-mapeditor">
    <div class="page-header">
      <div>
        <h1>Zoo Map Editor</h1>
        <p>Drag pins to reposition, click to edit details, then save to database.</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center;">
        <label style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;
          border-radius:var(--radius-sm);border:1px solid var(--border);
          background:var(--white);font-size:13px;font-weight:600;
          color:var(--text-mid);cursor:pointer;transition:all var(--transition);"
          onmouseover="this.style.background='var(--green-pale)'"
          onmouseout="this.style.background='var(--white)'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Change Map Image
          <input type="file" accept="image/*" onchange="mapEditorChangeMap(event)" style="display:none;" />
        </label>
        <button class="btn btn-outline" onclick="mapEditorAddPin()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Pin
        </button>
        <button class="btn btn-primary" id="mapEditorSaveBtn" onclick="mapEditorSave()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
          Save to Database
        </button>
      </div>
    </div>

    <!-- Map + drawer layout -->
    <div style="display:flex;gap:16px;align-items:flex-start;">

      <!-- Map card -->
      <div class="card" style="flex:1;padding:0;overflow:hidden;line-height:0;">
        <div id="mapEditorContainer" style="position:relative;width:100%;cursor:default;border-radius:var(--radius);">
          <img id="mapEditorImg" alt="Zoo Map" draggable="false"
            style="width:100%;display:block;border-radius:var(--radius);pointer-events:none;" />
          <!-- pins injected by JS -->
        </div>
      </div>

      <!-- Edit drawer -->
      <div id="mapEditorDrawer" style="
        width:300px;flex-shrink:0;background:var(--white);border-radius:var(--radius);
        border:1px solid var(--border);box-shadow:var(--shadow);
        display:none;flex-direction:column;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);
          display:flex;align-items:center;justify-content:space-between;">
          <span id="mapDrawerTitle" style="font-size:14px;font-weight:700;color:var(--text-dark);"></span>
          <button onclick="mapEditorCloseDrawer()"
            style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--text-light);
            border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;"
            onmouseover="this.style.background='var(--green-bg)'" onmouseout="this.style.background='none'">✕</button>
        </div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:14px;overflow-y:auto;max-height:70vh;">
          <div class="form-group" style="margin:0;">
            <label>Name</label>
            <input class="form-input" id="meInputName" oninput="mapEditorSetField('name',this.value)" />
          </div>
          <div style="display:grid;grid-template-columns:1fr auto;gap:10px;">
            <div class="form-group" style="margin:0;">
              <label>Emoji</label>
              <input class="form-input" id="meInputEmoji" style="text-align:center;font-size:20px;"
                oninput="mapEditorSetField('emoji',this.value)" />
            </div>
            <div class="form-group" style="margin:0;">
              <label>Color</label>
              <input type="color" id="meInputColor"
                style="width:52px;height:38px;border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;padding:2px;display:block;"
                oninput="mapEditorSetField('color',this.value)" />
            </div>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Zone</label>
            <select class="form-input" id="meInputZone" onchange="mapEditorSetField('zone',this.value)"></select>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Description</label>
            <textarea class="form-input" id="meInputDesc" rows="3"
              style="resize:vertical;line-height:1.5;"
              oninput="mapEditorSetField('desc',this.value)"></textarea>
          </div>
          <div style="background:var(--green-bg);border-radius:var(--radius-sm);padding:8px 12px;
            font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="10" r="3"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
            Position: x=<span id="mePosX">0</span>%  y=<span id="mePosY">0</span>%
          </div>
          <button onclick="mapEditorDeletePin()" style="
            width:100%;padding:9px;border-radius:var(--radius-sm);
            border:1px solid #fca5a5;background:#fff5f5;color:var(--red);
            font-size:13px;font-weight:600;cursor:pointer;transition:all var(--transition);"
            onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align:middle;margin-right:4px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            Delete Pin
          </button>
        </div>
      </div>
    </div>

    <!-- Pin chips -->
    <div id="mapEditorChips" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;"></div>
  </section>

  <!-- Announcement Modal (create / edit) -->
<div class="modal-overlay" id="announcementModal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3 id="announcementModalTitle">New Announcement</h3>
      <button onclick="closeModal('announcementModal')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="amId" value=""/>
 
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label>Icon (emoji)</label>
          <input type="text" class="form-input" id="amIcon" value="📢" maxlength="4"
                 style="font-size:20px;text-align:center;"/>
        </div>
        <div class="form-group">
          <label>Icon Colour</label>
          <select class="form-input" id="amColor">
            <option value="orange">🟠 Orange (warning)</option>
            <option value="green">🟢 Green (info)</option>
            <option value="blue">🔵 Blue (notice)</option>
            <option value="purple">🟣 Purple (event)</option>
          </select>
        </div>
      </div>
 
      <div class="form-group">
        <label>Title *</label>
        <input type="text" class="form-input" id="amTitle" placeholder="e.g. Reptile House Closure"/>
      </div>
      <div class="form-group">
        <label>Message *</label>
        <textarea class="form-input" id="amBody" rows="3"
                  placeholder="Describe the announcement…"></textarea>
      </div>
      <div class="form-group">
        <label>Audience</label>
        <select class="form-input" id="amAudience">
          <option>All Visitors</option>
          <option>Members Only</option>
          <option>Staff Only</option>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select class="form-input" id="amActive">
          <option value="1">✅ Active (visible to visitors)</option>
          <option value="0">⏸ Draft (hidden)</option>
        </select>
      </div>
 
      <button class="btn btn-primary" style="width:100%;margin-top:8px;"
              onclick="submitAnnouncementModal()">
        <span id="amBtnText">Publish Announcement</span>
      </button>
    </div>
  </div>
</div>

</main>

<!-- Booking Details Modal -->
<div class="modal-overlay" id="bookingDetailsModal">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header">
      <h3>Booking Details</h3>
      <button onclick="closeModal('bookingDetailsModal')">✕</button>
    </div>
    <div class="modal-body" id="bdContent">
      <!-- populated by openBookingDetails() -->
    </div>
  </div>
</div>

<!-- Edit & Approve Modal -->
<div class="modal-overlay" id="editApproveModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header">
      <h3>Review &amp; Approve Booking</h3>
      <button onclick="closeModal('editApproveModal')">✕</button>
    </div>
    <div class="modal-body">

      <div id="eaProofThumb" style="margin-bottom:20px;display:none;">
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;">Payment Screenshot</p>
        <img id="eaProofImg" src="" alt="Payment proof"
             style="width:100%;max-height:220px;object-fit:contain;border-radius:10px;border:1px solid var(--border);background:var(--green-bg);"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'"/>
        <p style="display:none;color:var(--text-muted);font-size:13px;margin-top:6px;">⚠ Screenshot not accessible from this machine.</p>
      </div>

      <input type="hidden" id="eaBookingRef"/>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div style="grid-column:1/-1;">
          <label class="form-label">Booking Ref</label>
          <input id="eaRefDisplay" type="text" readonly class="form-input" style="background:var(--green-bg);color:var(--text-muted);"/>
        </div>
        <div>
          <label class="form-label">Visit Date</label>
          <input id="eaVisitDate" type="date" class="form-input"/>
        </div>
        <div style="display:flex;align-items:flex-end;">
          <p style="font-size:12px;color:var(--text-muted);padding-bottom:4px;">Change date only if visitor selected the wrong day.</p>
        </div>
        <div>
          <label class="form-label">Visitor Name</label>
          <input id="eaUsername" type="text" placeholder="Leave blank to keep" class="form-input"/>
        </div>
        <div>
          <label class="form-label">Visitor Email</label>
          <input id="eaEmail" type="email" placeholder="Leave blank to keep" class="form-input"/>
        </div>
      </div>

      <p style="font-size:12px;color:var(--text-muted);margin-top:14px;">
        ✏ Only change fields that need correcting. Blank fields keep the visitor's original values.
        Approving generates QR codes and notifies the visitor instantly.
      </p>

      <div style="display:flex;gap:10px;margin-top:22px;">
        <button class="btn-approve" style="flex:1;padding:12px;" onclick="submitApproval()">✓ Confirm &amp; Approve</button>
        <button class="btn-reject" style="padding:12px 20px;" onclick="closeModal('editApproveModal')">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Proof Modal -->
<div class="modal-overlay" id="proofModal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3>Payment Receipt — <span id="proofBookingRef"></span></h3>
      <button onclick="closeModal('proofModal')">✕</button>
    </div>
    <div class="modal-body" style="text-align:center;padding:24px;">
      <img id="proofImage" src="" alt="Payment proof"
           style="max-width:100%;max-height:70vh;border-radius:10px;border:1px solid var(--border);display:block;margin:0 auto;"/>
      <p id="proofImageError" style="display:none;color:var(--text-muted);margin-top:16px;">Could not load receipt image.</p>
      <div id="proofMeta" style="margin-top:16px;font-size:13px;color:var(--text-muted);text-align:left;background:var(--green-bg);border-radius:10px;padding:14px 18px;"></div>
    </div>
  </div>
</div>

<!-- Price Edit Modal -->
<div class="modal-overlay" id="priceModal">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header"><h3>Edit Ticket Price</h3><button onclick="closeModal('priceModal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="priceTicketType"/>
      <div class="form-group"><label>Category</label><input type="text" class="form-input" id="priceCategory" readonly/></div>
      <div class="form-group"><label>New Price (RM)</label><input type="number" class="form-input" id="priceValue" min="0.01" step="0.01" placeholder="0.00"/></div>
      <button class="btn btn-primary" style="width:100%;margin-top:8px;" onclick="savePrice()">Save &amp; Update Live</button>
    </div>
  </div>
</div>

<!-- Addon Price Edit Modal (NEW) -->
<div class="modal-overlay" id="addonPriceModal">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header"><h3>Edit Add-on Price</h3><button onclick="closeModal('addonPriceModal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="addonPriceKey"/>
      <div class="form-group"><label>Add-on</label><input type="text" class="form-input" id="addonPriceLabel" readonly/></div>
      <div class="form-group"><label>New Price (RM) per person</label><input type="number" class="form-input" id="addonPriceValue" min="0.01" step="0.01" placeholder="0.00"/></div>
      <button class="btn btn-primary" style="width:100%;margin-top:8px;" onclick="saveAddonPrice()">Save &amp; Update Live</button>
    </div>
  </div>
</div>

<!-- Feeding Cup Price Edit Modal -->
<div class="modal-overlay" id="feedingPriceModal">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header"><h3>Edit Feeding Cup Price</h3><button onclick="closeModal('feedingPriceModal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="feedingPriceKey"/>
      <div class="form-group"><label>Animal</label><input type="text" class="form-input" id="feedingPriceLabel" readonly/></div>
      <div class="form-group"><label>New Price (RM) per cup</label><input type="number" class="form-input" id="feedingPriceValue" min="0.01" step="0.01" placeholder="0.00"/></div>
      <p style="font-size:12px;color:var(--text-muted);margin-top:4px;">This price will reflect immediately on the Animal Feeding visitor page.</p>
      <button class="btn btn-primary" style="width:100%;margin-top:12px;" onclick="saveFeedingPrice()">Save &amp; Update Live</button>
    </div>
  </div>
</div>

<!-- Promo / Voucher Modal -->
<div class="modal-overlay" id="promoModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header"><h3>Add New Voucher</h3><button onclick="closeModal('promoModal')">✕</button></div>
    <div class="modal-body">
      <div class="form-group"><label>Voucher Code</label><input type="text" class="form-input" id="pmCode" placeholder="e.g. SUMMER25" style="text-transform:uppercase;"/></div>
      <div class="form-group"><label>Discount Type</label>
        <select class="form-input" id="pmDiscountType">
          <option value="fixed">Fixed Amount (RM)</option>
          <option value="percent">Percentage (%)</option>
        </select>
      </div>
      <div class="form-group"><label>Discount Value</label><input type="number" class="form-input" id="pmDiscountValue" min="0.01" step="0.01" placeholder="e.g. 10"/></div>
      <div class="form-group"><label>Minimum Spend (RM) <small style="color:var(--text-muted);font-weight:400;">0 = no minimum</small></label><input type="number" class="form-input" id="pmMinSpend" min="0" step="0.01" value="0"/></div>
      <div class="form-group"><label>Max Uses</label><input type="number" class="form-input" id="pmMaxUses" min="1" value="1"/></div>
      <div class="form-group"><label>Expires On <small style="color:var(--text-muted);font-weight:400;">leave blank = no expiry</small></label><input type="date" class="form-input" id="pmExpiresAt"/></div>
      <button class="btn btn-primary" style="width:100%;margin-top:8px;" onclick="submitNewVoucher()">Create Voucher</button>
    </div>
  </div>
</div>

<!-- Upload Modal -->
<div class="modal-overlay" id="uploadModal">
  <div class="modal" style="max-width:500px;">
    <div class="modal-header">
      <h3 id="uploadModalTitle">Upload New Slider Image</h3>
      <button onclick="closeModal('uploadModal')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="umEditId" value="">

      <div class="upload-drop-zone" id="uploadDropZone" onclick="document.getElementById('umFileInput').click()" style="cursor:pointer;">
        <span style="font-size:40px;">📁</span>
        <p id="dropZoneText">Drag & drop or click to choose a file</p>
        <small>PNG, JPG, WebP up to 10 MB</small>
        <input type="file" id="umFileInput" style="display:none;" accept="image/*" onchange="previewFile(this)"/>
      </div>
      <div id="umPreviewWrap" style="display:none;margin-top:10px;text-align:center;">
        <img id="umPreview" style="max-height:160px;border-radius:8px;object-fit:cover;width:100%;" src="" alt="preview"/>
      </div>

      <div style="text-align:center;margin:10px 0;font-size:12px;color:var(--text-muted);">— or paste an image URL instead —</div>
      <div class="form-group">
        <input type="text" id="umImageUrl" class="form-input" placeholder="https://example.com/photo.jpg" oninput="previewUrl(this.value)"/>
      </div>

      <div class="form-group"><label>Image Title *</label><input type="text" id="umTitle" class="form-input" placeholder="e.g. Bengal Tiger Display"/></div>
      <div class="form-group"><label>Alt Text</label><input type="text" id="umAlt" class="form-input" placeholder="Short description for accessibility"/></div>
      <div class="form-group">
        <label>Display Order <small style="color:var(--text-muted)">(lower number = shows first)</small></label>
        <input type="number" id="umOrder" class="form-input" value="0" min="0"/>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select id="umStatus" class="form-input">
          <option value="1">Live (show in slider)</option>
          <option value="0">Draft (hidden from visitors)</option>
        </select>
      </div>
      <button class="btn btn-primary" style="width:100%;margin-top:8px;" onclick="submitUploadModal()">
        <span id="uploadBtnText">Upload Image</span>
      </button>
    </div>
  </div>
</div>

<!-- Event Modal (Add / Edit) -->
<div class="modal-overlay" id="eventModal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3 id="eventModalTitle">Add New Event</h3>
      <button onclick="closeModal('eventModal')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="emId" />

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group" style="grid-column:1/-1;">
          <label>Event Name</label>
          <input type="text" id="emName" class="form-input" placeholder="e.g. Penguin Talk" />
        </div>
        <div class="form-group">
          <label>Session</label>
          <select id="emSession" class="form-input">
            <option value="morning">🌅 Morning</option>
            <option value="afternoon">☀️ Afternoon</option>
          </select>
        </div>
        <div class="form-group">
          <label>Time</label>
          <input type="time" id="emTime" class="form-input" />
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>Venue</label>
          <input type="text" id="emVenue" class="form-input" placeholder="e.g. Penguin Coast" />
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>
            Date
            <small style="font-weight:400;color:var(--text-muted);margin-left:6px;">
              — leave blank to show every day automatically
            </small>
          </label>
          <input type="date" id="emDate" class="form-input" />
          <small style="color:var(--text-muted);margin-top:4px;display:block;">
            📅 If a specific date is set, the event only shows on that day on the visitor page.
            Clear the date to make it a recurring daily event.
          </small>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select id="emActive" class="form-input">
            <option value="1">● Active</option>
            <option value="0">○ Inactive</option>
          </select>
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" id="emOrder" class="form-input" value="0" min="0" />
        </div>
      </div>

      <button class="btn btn-primary" style="width:100%;margin-top:12px;" onclick="submitEventModal()">
        <span id="emBtnText">Add Event</span>
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
  // ===== WILDTRACK ADMIN JS =====

async function doLogout() {
    const confirmed = confirm('Are you sure you want to logout?');
    if (!confirmed) return;
    try {
        await fetch('api/auth.php?action=logout', {method: 'POST', credentials: 'include'});
    } catch (error) {
        console.error("Logout request failed:", error);
    } finally {
        window.location.href = 'staff-login.php';
    }
}

// ---- NAVIGATION ----
function showPage(pageId) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

  const target = document.getElementById('page-' + pageId);
  if (target) target.classList.add('active');

  const navItem = document.querySelector(`[data-page="${pageId}"]`);
  if (navItem) navItem.classList.add('active');

  const titles = {
    overview: 'Overview', ticketing: 'Ticketing', feedback: 'Feedback & Reviews',
    media: 'Media Gallery', events: 'Events Management', staff: 'Staff',
    reports: 'Reports', announcements: 'Announcements', settings: 'Settings',
    profile: 'Profile', mapeditor: 'Zoo Map Editor'
  };
  document.getElementById('pageTitle').textContent = titles[pageId] || pageId;

  // Lazy-init charts
  if (pageId === 'overview')   initOverviewCharts();
  if (pageId === 'reports')    initReportsChart();
  if (pageId === 'settings')   loadNotifPrefs();
  if (pageId === 'mapeditor')  initMapEditor();

  // Close notif panel
  document.getElementById('notifPanel').classList.remove('open');
}

// Nav click — wrapped in DOMContentLoaded so DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.nav-item[data-page]').forEach(item => {
    item.addEventListener('click', e => {
      e.preventDefault();
      showPage(item.dataset.page);
    });
  });
  initAdminIdentity();
  startLiveClock();
  startAutoRefresh();
});

// ---- LIVE CLOCK & GREETING ----
function getGreeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good morning';
  if (h < 17) return 'Good afternoon';
  return 'Good evening';
}

function formatAdminDate(d) {
  return d.toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

let _adminDisplayName = 'Admin';

function updateGreeting() {
  const el = document.getElementById('overviewGreeting');
  if (el) el.textContent = formatAdminDate(new Date()) + ' \u2014 ' + getGreeting() + ', ' + _adminDisplayName;
}

function startLiveClock() {
  updateGreeting();
  setInterval(updateGreeting, 60000); // refresh every minute
}

// ---- SESSION / ADMIN IDENTITY ----
async function initAdminIdentity() {
  try {
    const res  = await fetch('api/tickets.php?action=get_session', { credentials: 'include' });
    const data = await res.json();
    if (data.success && data.username) {
      const name = data.username;
      // Build initials from up to 2 words
      const initials = name.trim().split(/\s+/).map(w => w[0].toUpperCase()).slice(0, 2).join('');

      _adminDisplayName = name;

      const avatarEl = document.getElementById('sidebarAvatar');
      const nameEl   = document.getElementById('sidebarUsername');
      if (avatarEl) avatarEl.textContent = initials;
      if (nameEl)   nameEl.textContent   = name;
      updateGreeting(); // re-render with real name immediately
    }
  } catch (_) {
    // Session endpoint not yet available — keep defaults
  }
}

// ---- SIDEBAR TOGGLE ----
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}

// ---- NOTIFICATIONS ----
function toggleNotifications(e) {
  e.stopPropagation(); // prevent the document click handler from immediately closing
  const panel = document.getElementById('notifPanel');
  const wasOpen = panel.classList.contains('open');
  panel.classList.toggle('open');
  if (!wasOpen) loadAdminNotifications(); // refresh on every open
}
document.addEventListener('click', e => {
  const panel = document.getElementById('notifPanel');
  const btn   = document.querySelector('.notif-btn');
  if (panel && panel.classList.contains('open') && btn
      && !panel.contains(e.target) && !btn.contains(e.target)) {
    panel.classList.remove('open');
  }
});
// markAllRead — calls server then re-renders so read state is accurate
async function markAllRead() {
  // Stop click from bubbling to the outside-click handler
  event?.stopPropagation?.();
  try {
    await fetch('api/notifications_admin.php?action=mark_all_read', { method: 'POST', credentials: 'include' });
  } catch(_) {}
  await loadAdminNotifications();
  showToast('All notifications marked as read ✓');
}

// ---- TABS ----
function switchTab(btn, tabId) {
  const bar = btn.closest('.tab-bar');
  bar.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');

  const page = btn.closest('.page');
  page.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
  const target = document.getElementById(tabId);
  if (target) target.classList.add('active');

  // Lazy-load dynamic tabs
  if (tabId === 'tab-pricing')    { loadPricingTable(); loadAddonPricingTable(); loadFeedingPricingTable(); }
  if (tabId === 'tab-promotions') loadVouchersTable();
  if (tabId === 'tab-approvals')  { filterApprovalTable(); }
}

// ── Approval view: 'recent' (default) or 'history' ──
let _approvalView = 'recent'; // default: pending + last 3 days

function switchApprovalView(view) {
  _approvalView = view;
  const recentBtn  = document.getElementById('approvalSubRecent');
  const historyBtn = document.getElementById('approvalSubHistory');
  const label      = document.getElementById('approvalViewLabel');
  const title      = document.getElementById('approvalCardTitle');

  if (view === 'recent') {
    if (recentBtn)  { recentBtn.className  = 'btn btn-primary'; recentBtn.style.cssText  = 'font-size:12px;padding:7px 16px;'; }
    if (historyBtn) { historyBtn.className = 'btn btn-outline';  historyBtn.style.cssText = 'font-size:12px;padding:7px 16px;'; }
    if (label) label.textContent = 'Showing pending + last 3 days of approved/rejected';
    if (title) title.textContent = 'Pending & Recent Payments';
  } else {
    if (recentBtn)  { recentBtn.className  = 'btn btn-outline';  recentBtn.style.cssText  = 'font-size:12px;padding:7px 16px;'; }
    if (historyBtn) { historyBtn.className = 'btn btn-primary';  historyBtn.style.cssText = 'font-size:12px;padding:7px 16px;'; }
    if (label) label.textContent = 'All approved/rejected records older than 3 days';
    if (title) title.textContent = 'Payment History';
  }
  filterApprovalTable();
}

// ── Filter + render approval table based on view, search, status ──
function filterApprovalTable() {
  const q         = (document.getElementById('approvalSearch')?.value || '').toLowerCase().trim();
  const statusF   = document.getElementById('approvalStatusFilter')?.value || '';
  const cutoff    = new Date(); cutoff.setDate(cutoff.getDate() - 3); // 3 days ago

  const filtered = _allPayments.filter(p => {
    const purchaseDate = p.purchase_date ? new Date(p.purchase_date) : null;
    const isPending    = p.status === 'pending';
    const isOld        = purchaseDate && purchaseDate < cutoff;

    // View filter
    if (_approvalView === 'recent') {
      // Show: ALL pending (no age limit) + approved/rejected within 3 days
      if (!isPending && isOld) return false;
    } else {
      // History: only approved/rejected older than 3 days
      if (isPending) return false;
      if (!isOld)    return false;
    }

    // Status filter
    if (statusF && p.status !== statusF) return false;

    // Search filter — TXN ID or customer name/email
    if (q) {
      const ref   = (p.booking_ref  || '').toLowerCase();
      const name  = (p.visitor_name || p.username || '').toLowerCase();
      const email = (p.visitor_email || p.email   || '').toLowerCase();
      if (!ref.includes(q) && !name.includes(q) && !email.includes(q)) return false;
    }

    return true;
  });

  renderApprovalTable(filtered);

  // Update pagination info
  const pag  = document.getElementById('approvalPagination');
  const info = document.getElementById('approvalPaginationInfo');
  if (pag && info) {
    pag.style.display = filtered.length ? 'flex' : 'none';
    info.textContent  = filtered.length + ' record' + (filtered.length !== 1 ? 's' : '') + ' shown';
  }
}

// ---- TICKET ACTIONS (LIVE — connected to database) ----

let _allPayments = [];

async function loadPendingPayments() {
  try {
    const res = await fetch('api/tickets.php?action=get_pending', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) {
      if (res.status === 401 || res.status === 403) {
        // Not logged in as admin — silently skip, don't redirect mid-page
        return;
      }
      showToast(data.message || 'Failed to load payments.', 'error');
      return;
    }
    _allPayments = data.payments || [];
    filterApprovalTable();   // FIX 4: apply 3-day / history filter instead of showing all
    renderOverviewApprovals(_allPayments.filter(p => p.status === 'pending').slice(0, 4));
    const pendingCount = _allPayments.filter(p => p.status === 'pending').length;
    const tabBadge = document.getElementById('tabPendingCount');
    if (tabBadge) tabBadge.textContent = pendingCount;
    const navBadge = document.querySelector('.nav-badge');
    if (navBadge) { navBadge.textContent = pendingCount; navBadge.style.display = pendingCount > 0 ? '' : 'none'; }
    document.querySelectorAll('.stat-card').forEach(card => {
      if (card.querySelector('.stat-label')?.textContent === 'Pending Approvals') {
        const val = card.querySelector('.stat-value');
        if (val) val.textContent = pendingCount;
      }
    });
  } catch (err) { showToast('Network error loading payments.', 'error'); }
}

function renderApprovalTable(payments) {
  const tbody = document.getElementById('approvalTableBody');
  if (!tbody) return;
  if (payments.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text-muted);">No payment records found.</td></tr>';
    return;
  }
  tbody.innerHTML = payments.map(p => {
    // Use visitor_name/visitor_email (the actual ticket buyer), fall back to username/email
    const customerName  = p.visitor_name  || p.username || '—';
    const customerEmail = p.visitor_email || p.email    || '';

    const proofBtn = p.payment_proof
      ? `<button class="btn-view-proof" onclick="event.stopPropagation();viewProof('${p.payment_proof}','${p.booking_ref}','${customerName}','${p.visit_date}')">View Screenshot</button>`
      : '<span style="color:var(--text-muted);font-size:12px;">No file</span>';

    const dateStr    = p.purchase_date ? p.purchase_date.split(' ')[0] : '—';
    const statusBadge = `<span class="status-badge ${p.status}">${p.status.charAt(0).toUpperCase()+p.status.slice(1)}</span>`;

    // Build ticket summary: prefer ticket_breakdown (array), else ticket_types string
    // Friendly names for DB ticket_type values
    const _typeLabels = { Adult: 'Adult Pass', Child: 'Child Pass', Senior: 'Senior Pass', Group: 'Family Bundle' };
    let ticketSummary = '—';
    if (p.ticket_breakdown && Array.isArray(p.ticket_breakdown) && p.ticket_breakdown.length) {
      ticketSummary = p.ticket_breakdown
        .map(t => `${_typeLabels[t.ticket_type] || t.ticket_type} ×${t.qty}`)
        .join(', ');
    } else if (p.ticket_types) {
      // Strip any leading/trailing commas or spaces (DB concatenation artefact)
      const cleaned = p.ticket_types.replace(/^[\s,]+|[\s,]+$/g, '');
      const typeList = cleaned ? cleaned.split(',').map(s => s.trim()).filter(Boolean) : [];
      if (typeList.length > 0) {
        const qty = p.ticket_count || typeList.length;
        ticketSummary = typeList.map(t => _typeLabels[t] || t).join(', ') + ` ×${qty}`;
      }
    } else if (p.ticket_type) {
      ticketSummary = (_typeLabels[p.ticket_type] || p.ticket_type) + ` ×${p.ticket_count || 1}`;
    }

    // Total: final_total includes addons + voucher discount — most accurate
    const totalPrice = parseFloat(p.final_total || p.total_price || p.price || 0).toFixed(2);

    const actionCell = p.status === 'pending'
      ? `<button class="btn-approve" onclick="event.stopPropagation();approveTicket(this,'${p.booking_ref}')">✓ Approve</button>
         <button class="btn-reject"  onclick="event.stopPropagation();rejectTicket(this,'${p.booking_ref}')">✕ Reject</button>`
      : statusBadge;

    const approvedByCell = p.approved_by_name
      ? `<span style="font-weight:600;color:var(--green-dark);">👤 ${p.approved_by_name}</span>`
      : `<span style="color:var(--text-muted);font-size:12px;">—</span>`;

    return `<tr style="cursor:pointer;" onclick="openBookingDetails('${p.booking_ref}')" title="Click to view booking details">
      <td class="txn-id">${p.booking_ref}</td>
      <td><strong>${customerName}</strong><br/><small>${customerEmail}</small></td>
      <td>${ticketSummary}</td>
      <td><strong>RM${totalPrice}</strong></td>
      <td>${proofBtn}</td>
      <td>${dateStr}</td>
      <td>${statusBadge}</td>
      <td style="font-size:12px;">${approvedByCell}</td>
      <td class="action-cell">${actionCell}</td>
    </tr>`;
  }).join('');
}

function renderOverviewApprovals(payments) {
  const tbody = document.getElementById('overviewApprovalBody');
  if (!tbody) return;
  if (payments.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">No pending approvals.</td></tr>';
    return;
  }
  tbody.innerHTML = payments.map(p => `<tr>
    <td class="txn-id">${p.booking_ref}</td>
    <td><strong>${p.username}</strong></td>
    <td>${p.ticket_types || p.ticket_type || '—'} × ${p.ticket_count || 1}</td>
    <td><strong>RM${parseFloat(p.final_total || p.total_price || p.price || 0).toFixed(2)}</strong></td>
    <td class="action-cell">
      <button class="btn-approve" onclick="approveTicket(this,'${p.booking_ref}')">Approve</button>
      <button class="btn-reject"  onclick="rejectTicket(this,'${p.booking_ref}')">Reject</button>
    </td>
  </tr>`).join('');
}

function approveTicket(btn, bookingRef) {
  const row = _allPayments.find(p => p.booking_ref === bookingRef);

  // Use visitor fields (ticket buyer), not session admin fields
  const customerName  = row?.visitor_name  || row?.username || '';
  const customerEmail = row?.visitor_email || row?.email    || '';

  document.getElementById('eaBookingRef').value  = bookingRef;
  document.getElementById('eaRefDisplay').value  = bookingRef;
  document.getElementById('eaVisitDate').value   = row?.visit_date || '';
  document.getElementById('eaUsername').value    = customerName;
  document.getElementById('eaEmail').value       = customerEmail;

  const thumbWrap = document.getElementById('eaProofThumb');
  const thumbImg  = document.getElementById('eaProofImg');
  if (row?.payment_proof) {
    thumbImg.src = 'http://localhost/WildTrack/' + row.payment_proof;
    thumbWrap.style.display = 'block';
  } else {
    thumbWrap.style.display = 'none';
  }

  openModal('editApproveModal');
}

async function submitApproval() {
  const bookingRef = document.getElementById('eaBookingRef').value;
  const payload = {
    booking_ref: bookingRef,
    visit_date:  document.getElementById('eaVisitDate').value.trim(),
    username:    document.getElementById('eaUsername').value.trim(),
    email:       document.getElementById('eaEmail').value.trim(),
  };
  // Strip empty fields so backend COALESCE keeps original values
  Object.keys(payload).forEach(k => { if (payload[k] === '') delete payload[k]; });

  const confirmBtn = document.querySelector('#editApproveModal .btn-approve');
  confirmBtn.disabled = true; confirmBtn.textContent = 'Approving…';

  try {
    const res  = await fetch('api/tickets.php?action=approve_payment', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) {
      closeModal('editApproveModal');
      showToast(`${bookingRef} approved — visitor notified ✓`);
      await loadPendingPayments();
    } else {
      showToast(data.message || 'Approval failed.', 'error');
    }
  } catch (err) {
    showToast('Network error. Try again.', 'error');
  } finally {
    confirmBtn.disabled = false; confirmBtn.textContent = '✓ Confirm & Approve';
  }
}

async function rejectTicket(btn, bookingRef) {
  const reason = prompt(`Reason for rejecting ${bookingRef}?`, 'Payment could not be verified.');
  if (reason === null) return;
  btn.disabled = true; btn.textContent = 'Rejecting…';
  try {
    const res = await fetch('api/tickets.php?action=reject_payment', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ booking_ref: bookingRef, reason: reason || 'Payment could not be verified.' })
    });
    const data = await res.json();
    if (data.success) { showToast(`${bookingRef} rejected — visitor notified.`, 'error'); await loadPendingPayments(); }
    else { showToast(data.message || 'Rejection failed.', 'error'); btn.disabled = false; btn.textContent = '✕ Reject'; }
  } catch (err) { showToast('Network error. Try again.', 'error'); btn.disabled = false; btn.textContent = '✕ Reject'; }
}

// ---- BOOKING DETAILS MODAL (row click) ----
function openBookingDetails(bookingRef) {
  const p = _allPayments.find(x => x.booking_ref === bookingRef);
  if (!p) return;

  const customerName  = p.visitor_name  || p.username || '—';
  const customerEmail = p.visitor_email || p.email    || '—';
  const dateStr       = p.purchase_date ? p.purchase_date.split(' ')[0] : '—';
  const visitDate     = p.visit_date    || '—';
  // Use final_total (includes addons + voucher) as the authoritative paid amount
  const totalPrice    = parseFloat(p.final_total || p.total_price || p.price || 0).toFixed(2);
  const statusClass   = p.status || 'pending';
  const statusLabel   = p.status ? p.status.charAt(0).toUpperCase() + p.status.slice(1) : 'Pending';
  // Payment method: stored from Ticketing.php submission; default to TNG since it's the only option
  const paymentMethod = p.payment_method || "Touch 'n Go eWallet";

  // ── Ticket breakdown ──
  // Friendly display names for DB ticket_type values
  const typeLabels = { Adult: 'Adult Pass', Child: 'Child Pass', Senior: 'Senior Pass', Group: 'Family Bundle' };
  let ticketRows = '';
  let ticketSubtotal = 0;

  if (p.ticket_breakdown && Array.isArray(p.ticket_breakdown) && p.ticket_breakdown.length) {
    // Rich breakdown from API (preferred)
    p.ticket_breakdown.forEach(t => {
      const label     = typeLabels[t.ticket_type] || t.ticket_type;
      const lineTotal = parseFloat(t.price_per || 0) * parseInt(t.qty || 1);
      ticketSubtotal += lineTotal;
      ticketRows += `<div class="bd-row"><span>${label} ×${t.qty} @ RM${parseFloat(t.price_per||0).toFixed(2)}</span><span>RM${lineTotal.toFixed(2)}</span></div>`;
    });
  } else if (p.ticket_types) {
    // Fallback: comma-separated string like "Adult, Child" with ticket_count
    // Strip leading/trailing commas & spaces then split
    const rawTypes = p.ticket_types.replace(/^[\s,]+|[\s,]+$/g, '');
    const typeList = rawTypes ? rawTypes.split(',').map(s => s.trim()).filter(Boolean) : [];
    const totalQty = parseInt(p.ticket_count || typeList.length || 1);
    const qtyEach  = typeList.length > 0 ? Math.round(totalQty / typeList.length) : totalQty;
    const subtotal = parseFloat(p.total_price || p.price || 0);
    ticketSubtotal = subtotal;
    if (typeList.length > 0) {
      typeList.forEach(t => {
        const label = typeLabels[t] || t + ' Pass';
        ticketRows += `<div class="bd-row"><span>${label} ×${qtyEach}</span><span>—</span></div>`;
      });
      ticketRows += `<div class="bd-row" style="color:var(--text-muted);font-size:12px;"><span>Tickets subtotal</span><span>RM${subtotal.toFixed(2)}</span></div>`;
    } else {
      ticketRows = `<div class="bd-row" style="color:var(--text-muted)">No ticket type details available</div>`;
    }
  }

  // ── Add-on breakdown ──
  let addonRows = '';
  let addonTotal = 0;
  if (p.addons && Array.isArray(p.addons) && p.addons.length) {
    p.addons.forEach(a => {
      const lineTotal = parseFloat(a.price_per || 0) * parseInt(a.quantity || 1);
      addonTotal += lineTotal;
      addonRows += `<div class="bd-row"><span>${a.addon_type} ×${a.quantity} @ RM${parseFloat(a.price_per||0).toFixed(2)}</span><span>RM${lineTotal.toFixed(2)}</span></div>`;
    });
  }

  // ── Voucher discount ──
  let voucherRow = '';
  const discountAmt = parseFloat(p.discount_amount || p.voucher_discount || 0);
  const voucherCode  = p.voucher_code || '';
  if (voucherCode && discountAmt > 0) {
    voucherRow = `<div class="bd-row" style="color:var(--green-mid);">
      <span>Voucher (${voucherCode})</span><span>−RM${discountAmt.toFixed(2)}</span>
    </div>`;
  }

  // ── Payment proof ──
  const proofSection = p.payment_proof
    ? `<div style="margin-bottom:18px;">
        <p class="bd-section-label">Payment Screenshot</p>
        <img src="http://localhost/WildTrack/${p.payment_proof}" alt="Payment proof"
             style="width:100%;max-height:200px;object-fit:contain;border-radius:10px;border:1px solid var(--border);background:var(--green-bg);"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'"/>
        <p style="display:none;color:var(--text-muted);font-size:12px;margin-top:6px;">⚠ Screenshot not accessible from this machine.</p>
       </div>` : '';

  // ── Action buttons ──
  const actionBtns = p.status === 'pending'
    ? `<button class="btn-approve" style="flex:1;padding:12px;" onclick="closeModal('bookingDetailsModal');approveTicket(this,'${bookingRef}')">✓ Approve</button>
       <button class="btn-reject" style="padding:12px 20px;" onclick="closeModal('bookingDetailsModal');rejectTicket(this,'${bookingRef}')">✕ Reject</button>`
    : `<button class="btn-outline" style="flex:1;padding:12px;" onclick="closeModal('bookingDetailsModal')">Close</button>`;

  document.getElementById('bdContent').innerHTML = `
    ${proofSection}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
      <div class="bd-info-box"><div class="bd-info-label">Booking Ref</div><div class="bd-info-val">${p.booking_ref}</div></div>
      <div class="bd-info-box"><div class="bd-info-label">Status</div><div class="bd-info-val"><span class="status-badge ${statusClass}">${statusLabel}</span></div></div>
      <div class="bd-info-box"><div class="bd-info-label">Customer</div><div class="bd-info-val"><strong>${customerName}</strong><br/><small style="color:var(--text-muted)">${customerEmail}</small></div></div>
      <div class="bd-info-box"><div class="bd-info-label">Visit Date</div><div class="bd-info-val">${visitDate}</div></div>
      <div class="bd-info-box"><div class="bd-info-label">Purchase Date</div><div class="bd-info-val">${dateStr}</div></div>
      <div class="bd-info-box"><div class="bd-info-label">Payment Method</div><div class="bd-info-val">${paymentMethod}</div></div>
    </div>

    <p class="bd-section-label">Ticket Breakdown</p>
    <div class="bd-breakdown-box">
      ${ticketRows || '<div class="bd-row" style="color:var(--text-muted)">No ticket details</div>'}
      ${addonRows}
      ${voucherRow}
      <div class="bd-row bd-total-row"><span>Total Paid</span><span>RM${totalPrice}</span></div>
    </div>

    <div style="display:flex;gap:10px;margin-top:20px;">${actionBtns}</div>
  `;

  openModal('bookingDetailsModal');
}

function viewProof(proofPath, bookingRef, username, visitDate) {
  const img = document.getElementById('proofImage');
  const errMsg = document.getElementById('proofImageError');
  const meta = document.getElementById('proofMeta');
  const refSpan = document.getElementById('proofBookingRef');
  if (refSpan) refSpan.textContent = bookingRef || '';
  img.src = 'http://localhost/WildTrack/' + proofPath;
  img.style.display = 'block';
  if (errMsg) errMsg.style.display = 'none';
  img.onerror = () => { img.style.display = 'none'; if (errMsg) errMsg.style.display = 'block'; };
  if (meta) meta.innerHTML = `<strong>Booking:</strong> ${bookingRef||'—'}<br/><strong>Customer:</strong> ${username||'—'}<br/><strong>Visit Date:</strong> ${visitDate||'—'}`;
  openModal('proofModal');
}

function updatePendingCount(delta) { /* handled by loadPendingPayments */ }

// FIX 4: Dynamic admin notifications — fully DB-driven, replaces hardcoded panel
async function loadAdminNotifications() {
  try {
    const res  = await fetch('api/notifications_admin.php?action=get', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;

    const notifs      = data.notifications  || [];
    const unreadCount = data.unread_count   || 0;

    // Update the red dot indicator
    const dot = document.querySelector('.notif-dot');
    if (dot) dot.style.display = unreadCount > 0 ? '' : 'none';

    // Render notification items
    const list = document.getElementById('notifList');
    if (!list) return;

    if (!notifs.length) {
      list.innerHTML = '<div style="text-align:center;padding:24px 16px;color:var(--text-muted);font-size:13px;">✅ All caught up — no new notifications</div>';
      return;
    }

    const iconSVG = {
      ticket:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg>',
      calendar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
      message:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>',
      star:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12,2 15,9 22,9 17,14 19,21 12,16 5,21 7,14 2,9 9,9"/></svg>',
    };

    list.innerHTML = notifs.map(n => {
      const clickAttr = n.action
        ? `onclick="showPage('${n.action}');document.getElementById('notifPanel').classList.remove('open');"`
        : '';
      // n.sub is our internal label; n.body comes from DB rows — support both
      const bodyText = escA(n.sub || n.body || '');
      return `
        <div class="notif-item ${n.is_read ? '' : 'unread'}"
             style="cursor:${n.action ? 'pointer' : 'default'};"
             ${clickAttr}>
          <div class="notif-icon ${n.type}">${iconSVG[n.icon] || iconSVG.message}</div>
          <div>
            <strong>${escA(n.title || '')}</strong><br/>
            <small>${bodyText}</small>
          </div>
        </div>`;
    }).join('');

  } catch (_) {}
}

async function loadAllStats() {
  try {
    const res = await fetch('api/tickets.php?action=stats', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;

    const s = data;

    // Total tickets
    const totalEl = document.getElementById('statTotalTickets');
    if (totalEl) totalEl.textContent = s.total_tickets.toLocaleString();
    const totalBar = document.getElementById('statTotalTicketsBar');
    if (totalBar) totalBar.style.width = Math.min(100, (s.total_tickets / 500) * 100) + '%';

    // Total revenue
    const revEl = document.getElementById('statTotalRevenue');
    if (revEl) revEl.textContent = 'RM' + parseFloat(s.total_revenue).toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});

    // Pending count
    const pendingEl = document.getElementById('statPendingCount');
    if (pendingEl) pendingEl.textContent = s.pending_count;
    const pendingBar = document.getElementById('statPendingBar');
    if (pendingBar) pendingBar.style.width = Math.min(100, (s.pending_count / 20) * 100) + '%';
    const urgentEl = document.getElementById('statPendingUrgent');
    if (urgentEl) urgentEl.textContent = s.pending_count > 0 ? 'Urgent' : 'All Clear';

    // Today's tickets
    const todayEl = document.getElementById('statTodayTickets');
    if (todayEl) todayEl.textContent = s.today_tickets.toLocaleString();
    const todayBar = document.getElementById('statTodayBar');
    if (todayBar) todayBar.style.width = Math.min(100, (s.today_tickets / 100) * 100) + '%';

    // Also sync nav badge and Pending Approvals stat card
    const navBadge = document.querySelector('.nav-badge');
    if (navBadge) { navBadge.textContent = s.pending_count; navBadge.style.display = s.pending_count > 0 ? '' : 'none'; }

  } catch (_) {}
}

function startAutoRefresh() {
  try { loadAllStats(); } catch(e) {}
  try { loadPendingPayments(); } catch(e) {}
  try { loadAdminNotifications(); } catch(e) {}
  try { loadPricingTable(); } catch(e) {}
  try { loadAddonPricingTable(); } catch(e) {}
  try { loadFeedingPricingTable(); } catch(e) {}
  try { loadVouchersTable(); } catch(e) {}
  setInterval(() => {
    try { loadAllStats(); } catch(e) {}
    try { loadPendingPayments(); } catch(e) {}
    try { loadAdminNotifications(); } catch(e) {}
    try { loadVouchersTable(); } catch(e) {}
    // FIX 2: periodically refresh events so expired dates auto-deactivate while page is open
    try { if (document.getElementById('page-events')?.classList.contains('active')) loadEvents(); } catch(e) {}
  }, 30000);
}

// ---- PRICE EDIT (dynamic — saves to DB) ----

// DB ticket_type value for each admin key
const adminTypeMap = {
  adult: 'Adult', child: 'Child', senior: 'Senior', family: 'Group'
};
const ageRangeMap = {
  adult: '13 – 64 years', child: '4 – 12 years', senior: '65+ years', family: '2 Adults + 1 Child + 1 Senior'
};
const descMap = {
  adult: 'Full access · Best Seller', child: 'Interactive Map Included · Under 4 free',
  senior: 'Full access', family: 'Priority Entry · Save 15%'
};

async function loadPricingTable() {
  try {
    const res  = await fetch('api/tickets.php?action=get_prices', { credentials: 'include' });
    const data = await res.json();
    const tbody = document.getElementById('pricingTableBody');
    if (!tbody) return;
    if (!data.success || !data.prices.length) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">No prices found. Run the SQL file first.</td></tr>';
      return;
    }
    // Map DB type back to admin key
    const reverseMap = { Adult:'adult', Child:'child', Senior:'senior', Group:'family' };
    tbody.innerHTML = data.prices.map(row => {
      const key  = reverseMap[row.ticket_type] || row.ticket_type.toLowerCase();
      const age  = ageRangeMap[key]  || '—';
      const desc = descMap[key] || row.description || '—';
      return `<tr>
        <td><strong>${row.label}</strong></td>
        <td>${desc}</td>
        <td>${age}</td>
        <td class="price-cell" id="price-${key}"><strong>RM${parseFloat(row.price).toFixed(2)}</strong></td>
        <td><button class="btn-edit" onclick="editPrice('${key}','${row.ticket_type}','RM${parseFloat(row.price).toFixed(2)}')">Edit Price</button></td>
      </tr>`;
    }).join('');
  } catch(e) { console.error('loadPricingTable error', e); }
}

let currentPriceCategory = '';
let currentPriceDBType   = '';
function editPrice(category, dbType, currentPrice) {
  currentPriceCategory = category;
  currentPriceDBType   = dbType;
  document.getElementById('priceTicketType').value = dbType;
  document.getElementById('priceCategory').value   = dbType === 'Group' ? 'Family Bundle' : dbType + ' Pass';
  document.getElementById('priceValue').value      = currentPrice.replace('RM','');
  openModal('priceModal');
}

async function savePrice() {
  const val    = parseFloat(document.getElementById('priceValue').value);
  const dbType = document.getElementById('priceTicketType').value;
  if (isNaN(val) || val <= 0) { showToast('Please enter a valid price.', 'error'); return; }

  const btn = document.querySelector('#priceModal .btn-primary');
  btn.disabled = true; btn.textContent = 'Saving…';

  try {
    const res  = await fetch('api/tickets.php?action=save_price', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ticket_type: dbType, price: val })
    });
    const data = await res.json();
    if (data.success) {
      closeModal('priceModal');
      showToast(`${dbType} price updated to RM${val.toFixed(2)} — live on visitor page ✓`);
      await loadPricingTable();
    } else {
      showToast(data.message || 'Failed to save price.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.disabled = false; btn.textContent = 'Save & Update Live'; }
}

// ---- ADDON PRICE EDIT (NEW — connects to api/addon_prices.php) ----

const addonDescMap = {
  safari:  'Safari Shuttle ride through the park',
  feeding: 'Animal Feeding Pass (weekends & public holidays)'
};

async function loadAddonPricingTable() {
  const tbody = document.getElementById('addonPricingTableBody');
  if (!tbody) return;
  try {
    const res  = await fetch('api/addon_prices.php?action=get', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted);">Could not load add-on prices.</td></tr>';
      return;
    }
    tbody.innerHTML = Object.entries(data.prices).map(([key, row]) => `<tr>
      <td><strong>${row.label}</strong></td>
      <td>${addonDescMap[key] || '—'}</td>
      <td class="price-cell" id="addon-price-${key}"><strong>RM${parseFloat(row.price).toFixed(2)}</strong></td>
      <td><button class="btn-edit" onclick="editAddonPrice('${key}','${row.label}','${parseFloat(row.price).toFixed(2)}')">Edit Price</button></td>
    </tr>`).join('');
  } catch(e) { console.error('loadAddonPricingTable error', e); }
}

function editAddonPrice(key, label, currentPrice) {
  document.getElementById('addonPriceKey').value   = key;
  document.getElementById('addonPriceLabel').value = label;
  document.getElementById('addonPriceValue').value = currentPrice;
  openModal('addonPriceModal');
}

async function saveAddonPrice() {
  const key   = document.getElementById('addonPriceKey').value;
  const val   = parseFloat(document.getElementById('addonPriceValue').value);
  if (isNaN(val) || val <= 0) { showToast('Please enter a valid price.', 'error'); return; }

  const btn = document.querySelector('#addonPriceModal .btn-primary');
  btn.disabled = true; btn.textContent = 'Saving…';

  try {
    const res  = await fetch('api/addon_prices.php?action=save', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key, price: val })
    });
    const data = await res.json();
    if (data.success) {
      // Mirror feeding pass price to localStorage so animalFeeding.php stays in sync
      if (key === 'feeding') {
        const stored = JSON.parse(localStorage.getItem('wildtrack_feeding_prices') || '{}');
        stored['feeding_pass'] = val;
        localStorage.setItem('wildtrack_feeding_prices', JSON.stringify(stored));
      }
      closeModal('addonPriceModal');
      showToast(`Add-on price updated to RM${val.toFixed(2)} — live on visitor page ✓`);
      await loadAddonPricingTable();
    } else {
      showToast(data.message || 'Failed to save add-on price.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.disabled = false; btn.textContent = 'Save & Update Live'; }
}

// ---- FEEDING CUP PRICE EDIT (stored in localStorage, shared with visitor page) ----

const feedingAnimals = {
  feeding_goat:   { label: '🐐 Goat',   time: '11:30am – 12:00pm', default: 3.00 },
  feeding_sheep:  { label: '🐑 Sheep',  time: '12:00pm – 12:30pm', default: 3.00 },
  feeding_rabbit: { label: '🐇 Rabbit', time: '12:30pm – 1:00pm',  default: 2.00 },
};

function getFeedingPrices() {
  try {
    const stored = localStorage.getItem('wildtrack_feeding_prices');
    return stored ? JSON.parse(stored) : {};
  } catch(e) { return {}; }
}

function setFeedingPrice(key, val) {
  const prices = getFeedingPrices();
  prices[key] = val;
  localStorage.setItem('wildtrack_feeding_prices', JSON.stringify(prices));
}

function loadFeedingPricingTable() {
  const tbody = document.getElementById('feedingPricingTableBody');
  if (!tbody) return;
  const stored = getFeedingPrices();
  tbody.innerHTML = Object.entries(feedingAnimals).map(([key, info]) => {
    const price = stored[key] ?? info.default;
    return `<tr>
      <td><strong>${info.label}</strong></td>
      <td style="color:var(--text-muted);">🕛 ${info.time}</td>
      <td class="price-cell" id="price-${key}"><strong>RM${parseFloat(price).toFixed(2)}</strong></td>
      <td><button class="btn-edit" onclick="editFeedingPrice('${key}','${info.label}','${parseFloat(price).toFixed(2)}')">Edit Price</button></td>
    </tr>`;
  }).join('');
}

function editFeedingPrice(key, label, currentPrice) {
  document.getElementById('feedingPriceKey').value   = key;
  document.getElementById('feedingPriceLabel').value = label;
  document.getElementById('feedingPriceValue').value = currentPrice;
  openModal('feedingPriceModal');
}

function saveFeedingPrice() {
  const key = document.getElementById('feedingPriceKey').value;
  const val = parseFloat(document.getElementById('feedingPriceValue').value);
  if (isNaN(val) || val <= 0) { showToast('Please enter a valid price.', 'error'); return; }

  setFeedingPrice(key, val);
  closeModal('feedingPriceModal');
  showToast(`Feeding cup price updated to RM${val.toFixed(2)} — live on visitor page ✓`);
  loadFeedingPricingTable();
}

async function loadVouchersTable() {
  try {
    const res  = await fetch('api/tickets.php?action=get_vouchers', { credentials: 'include' });
    const data = await res.json();

    // Always update overview widget (pass empty array on failure)
    renderOverviewPromos(data.success ? (data.vouchers || []) : []);

    const tbody = document.getElementById('vouchersTableBody');
    if (!tbody) return;

    if (!data.success) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Failed to load vouchers.</td></tr>';
      return;
    }
    if (!data.vouchers.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">No vouchers yet. Click + Add Voucher to create one.</td></tr>';
      return;
    }
    tbody.innerHTML = data.vouchers.map(v => {
      const discountLabel = v.discount_type === 'percent'
        ? `${v.discount_value}%` : `RM${parseFloat(v.discount_value).toFixed(2)}`;
      const expiryLabel = v.expires_at ? v.expires_at : 'No expiry';
      const usesLabel   = `${v.used_count} / ${v.max_uses}`;
      const activeClass = v.is_active == 1 ? 'approved' : 'rejected';
      const activeLabel = v.is_active == 1 ? 'Active' : 'Inactive';
      const toggleLabel = v.is_active == 1 ? 'Deactivate' : 'Activate';
      return `<tr>
        <td><strong style="letter-spacing:0.5px;">${v.code}</strong></td>
        <td>${v.discount_type === 'percent' ? 'Percent' : 'Fixed'}</td>
        <td><strong style="color:var(--green-dark);">${discountLabel} off</strong></td>
        <td>${parseFloat(v.min_spend) > 0 ? 'RM' + parseFloat(v.min_spend).toFixed(2) : '—'}</td>
        <td>${usesLabel}</td>
        <td>${expiryLabel}</td>
        <td><span class="status-badge ${activeClass}">${activeLabel}</span></td>
        <td class="action-cell">
          <button class="btn-edit" onclick="toggleVoucher(${v.id})">${toggleLabel}</button>
          <button class="btn-reject" onclick="deleteVoucher(${v.id},'${v.code}')">Delete</button>
        </td>
      </tr>`;
    }).join('');
  } catch(e) {
    console.error('loadVouchersTable error', e);
    renderOverviewPromos([]); // clear loading state even on network error
  }
}

function renderOverviewPromos(vouchers) {
  const list = document.getElementById('overviewPromoList');
  if (!list) return;
  const colors = ['green-bg', 'blue-bg', 'amber-bg'];
  // Only show active vouchers, max 4
  const active = vouchers.filter(v => v.is_active == 1).slice(0, 4);
  if (!active.length) {
    list.innerHTML =
      '<div class="promo-item" style="color:var(--text-muted);font-size:13px;padding:14px 16px;">No active promotions.</div>' +
      '<button class="btn-outline-full" onclick="goOverviewTicketing()">+ Create New Promotion</button>';
    return;
  }
  const today = new Date(); today.setHours(0,0,0,0);
  list.innerHTML = active.map((v, i) => {
    const colorClass = colors[i % colors.length];
    const discountLabel = v.discount_type === 'percent'
      ? v.discount_value + '% Off'
      : 'RM' + parseFloat(v.discount_value).toFixed(2) + ' Off';
    const minLabel = parseFloat(v.min_spend) > 0
      ? ' (min spend RM' + parseFloat(v.min_spend).toFixed(2) + ')' : '';
    let expiryLabel = 'Ongoing';
    if (v.expires_at) {
      const exp = new Date(v.expires_at); exp.setHours(0,0,0,0);
      const diff = Math.round((exp - today) / 86400000);
      expiryLabel = diff < 0 ? 'Expired' : diff === 0 ? 'Expires today' : 'Ends in: ' + diff + ' day' + (diff !== 1 ? 's' : '');
    }
    return '<div class="promo-item ' + colorClass + '">' +
      '<div class="promo-badge">' + v.code + '</div>' +
      '<div class="promo-title">' + discountLabel + minLabel + '</div>' +
      '<div class="promo-sub">' + expiryLabel + '</div>' +
      '</div>';
  }).join('') +
  '<button class="btn-outline-full" onclick="goOverviewTicketing()">+ Create New Promotion</button>';

  // Update the Active Promotions count in the stat card
  document.querySelectorAll('.stat-card').forEach(card => {
    if (card.querySelector('.stat-label')?.textContent === 'Active Promotions') {
      const val = card.querySelector('.stat-value');
      if (val) val.textContent = String(active.length).padStart(2,'0');
    }
  });
}

async function submitNewVoucher() {
  const code     = document.getElementById('pmCode').value.trim().toUpperCase();
  const dtype    = document.getElementById('pmDiscountType').value;
  const dvalue   = parseFloat(document.getElementById('pmDiscountValue').value);
  const minSpend = parseFloat(document.getElementById('pmMinSpend').value) || 0;
  const maxUses  = parseInt(document.getElementById('pmMaxUses').value)    || 1;
  const expires  = document.getElementById('pmExpiresAt').value || null;

  if (!code)             { showToast('Voucher code is required.', 'error'); return; }
  if (!dvalue || dvalue <= 0) { showToast('Discount value must be > 0.', 'error'); return; }

  const btn = document.querySelector('#promoModal .btn-primary');
  btn.disabled = true; btn.textContent = 'Creating…';

  try {
    const res  = await fetch('api/tickets.php?action=save_voucher', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code, discount_type: dtype, discount_value: dvalue, min_spend: minSpend, max_uses: maxUses, expires_at: expires })
    });
    const data = await res.json();
    if (data.success) {
      closeModal('promoModal');
      showToast(`Voucher ${code} created ✓`);
      // Clear form
      ['pmCode','pmDiscountValue','pmMinSpend','pmExpiresAt'].forEach(id => { const el = document.getElementById(id); if(el) el.value = ''; });
      document.getElementById('pmMaxUses').value = '1';
      await loadVouchersTable();
    } else {
      showToast(data.message || 'Failed to create voucher.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.disabled = false; btn.textContent = 'Create Voucher'; }
}

async function deleteVoucher(id, code) {
  if (!confirm(`Delete voucher "${code}"? This cannot be undone.`)) return;
  try {
    const res  = await fetch('api/tickets.php?action=delete_voucher', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) { showToast(`Voucher ${code} deleted.`, 'error'); await loadVouchersTable(); }
    else showToast(data.message || 'Delete failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}

async function toggleVoucher(id) {
  try {
    const res  = await fetch('api/tickets.php?action=toggle_voucher', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.is_active ? 'Voucher activated.' : 'Voucher deactivated.');
      await loadVouchersTable();
    } else showToast(data.message || 'Toggle failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}

// ---- MEDIA ----
function escHtml(str) {
  const d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML;
}

async function loadMediaGallery() {
  const grid = document.getElementById('mediaGrid');
  if (!grid) return;
  grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">Loading…</div>';
  try {
    const res  = await fetch('api/media.php?action=list', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) { grid.innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:red;">Failed to load images.</div>'; return; }
    renderMediaGrid(data.images);
  } catch(e) {
    grid.innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:red;">Network error.</div>';
  }
}

function renderMediaGrid(images) {
  const grid  = document.getElementById('mediaGrid');
  const count = document.getElementById('mediaCount');
  const pag   = document.getElementById('mediaPagination');
  if (count) count.textContent = `Showing ${images.length} image${images.length !== 1 ? 's' : ''}`;
  if (pag)   pag.style.display = images.length ? '' : 'none';

  let html = images.map(img => {
    const isLive = img.show_in_slider == 1;
    return `
    <div class="media-card" data-id="${img.id}">
      <div class="media-img-wrap">
        <img src="${escHtml(img.image_url)}" alt="${escHtml(img.alt_text)}"
             style="width:100%;height:100%;object-fit:cover;"
             onerror="this.style.display='none'">
        <span class="media-status ${isLive ? 'live' : 'draft'}">${isLive ? 'LIVE' : 'DRAFT'}</span>
      </div>
      <div class="media-info">
        <div class="media-title-row"><strong>${escHtml(img.title)}</strong><span class="media-id">#${img.id}</span></div>
        <div class="media-meta">Order: ${img.sort_order}</div>
        <div class="media-meta">Uploaded: ${img.uploaded_at ? img.uploaded_at.slice(0,10) : '—'}</div>
        <div style="display:flex;gap:8px;margin-top:10px;">
          <button class="btn-approve" style="flex:1" onclick="toggleSliderImage(${img.id})">
            ${isLive ? 'Set Draft' : 'Set Live'}
          </button>
          <button class="btn-edit" onclick="editMedia(${img.id})">Edit</button>
          <button class="btn-reject-sm" onclick="deleteMediaImage(${img.id})">✕</button>
        </div>
      </div>
    </div>`;
  }).join('');

  html += `<div class="media-card add-media-card" onclick="openUploadModal()">
    <div class="add-media-inner">
      <span style="font-size:32px;opacity:0.4;">+</span>
      <span>Add New Photo</span>
      <span style="font-size:12px;opacity:0.5;">PNG, JPG up to 10MB</span>
    </div>
  </div>`;
  grid.innerHTML = html;
}

async function toggleSliderImage(id) {
  try {
    const res  = await fetch('api/media.php?action=toggle', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) { showToast(data.show_in_slider ? 'Set to LIVE ✓' : 'Set to DRAFT'); await loadMediaGallery(); }
    else showToast(data.message || 'Toggle failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}

async function deleteMediaImage(id) {
  if (!confirm('Delete this image? This cannot be undone.')) return;
  try {
    const res  = await fetch('api/media.php?action=delete', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) { showToast('Image deleted.', 'error'); await loadMediaGallery(); }
    else showToast(data.message || 'Delete failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}

async function editMedia(id) {
  try {
    const res  = await fetch('api/media.php?action=list', { credentials: 'include' });
    const data = await res.json();
    const img  = data.images?.find(i => i.id == id);
    if (!img) { showToast('Image not found.', 'error'); return; }
    document.getElementById('uploadModalTitle').textContent = 'Edit Slider Image';
    document.getElementById('uploadBtnText').textContent    = 'Save Changes';
    document.getElementById('umEditId').value   = img.id;
    document.getElementById('umTitle').value    = img.title;
    document.getElementById('umAlt').value      = img.alt_text;
    document.getElementById('umImageUrl').value = img.image_url;
    document.getElementById('umOrder').value    = img.sort_order;
    document.getElementById('umStatus').value   = img.show_in_slider;
    document.getElementById('umPreview').src    = img.image_url;
    document.getElementById('umPreviewWrap').style.display = '';
    openModal('uploadModal');
  } catch(e) { showToast('Network error.', 'error'); }
}

function previewFile(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('umPreview').src = e.target.result;
    document.getElementById('umPreviewWrap').style.display = '';
    document.getElementById('dropZoneText').textContent = input.files[0].name;
    document.getElementById('umImageUrl').value = '';
  };
  reader.readAsDataURL(input.files[0]);
}

function previewUrl(url) {
  if (!url) return;
  document.getElementById('umPreview').src = url;
  document.getElementById('umPreviewWrap').style.display = '';
  document.getElementById('umFileInput').value = '';
}

async function submitUploadModal() {
  const editId = document.getElementById('umEditId').value;
  const title  = document.getElementById('umTitle').value.trim();
  const alt    = document.getElementById('umAlt').value.trim();
  const url    = document.getElementById('umImageUrl').value.trim();
  const order  = document.getElementById('umOrder').value;
  const status = document.getElementById('umStatus').value;
  const file   = document.getElementById('umFileInput').files[0];
  const btn    = document.getElementById('uploadBtnText');

  if (!title) { showToast('Title is required.', 'error'); return; }
  if (!editId && !url && !file) { showToast('Please choose a file or paste an image URL.', 'error'); return; }

  btn.textContent = editId ? 'Saving…' : 'Uploading…';
  try {
    if (file) {
      // File upload via FormData → upload_slider.php
      const fd = new FormData();
      fd.append('image', file);
      fd.append('title', title);
      fd.append('alt_text', alt);
      fd.append('sort_order', order);
      fd.append('show_in_slider', status);
      if (editId) fd.append('edit_id', editId);
      const res  = await fetch('upload_slider.php', { method: 'POST', credentials: 'include', body: fd });
      const data = await res.json();
      if (data.success) { closeModal('uploadModal'); showToast(editId ? 'Image updated ✓' : 'Image uploaded ✓'); await loadMediaGallery(); }
      else showToast(data.message || 'Upload failed.', 'error');
    } else {
      // URL-only → api/media.php
      const payload = { title, alt_text: alt, image_url: url, sort_order: parseInt(order), show_in_slider: parseInt(status) };
      if (editId) payload.id = parseInt(editId);
      const action = editId ? 'update' : 'upload';
      const res    = await fetch(`api/media.php?action=${action}`, {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) { closeModal('uploadModal'); showToast(editId ? 'Image updated ✓' : 'Image added ✓'); await loadMediaGallery(); }
      else showToast(data.message || 'Failed.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.textContent = editId ? 'Save Changes' : 'Upload Image'; }
}

// ---- EVENTS MANAGEMENT ----

let _allEvents = [];

async function loadEvents() {
  try {
    // FIX 2: pass admin=1 so the API returns all events (including past-date) for management
    const res  = await fetch('api/events.php?action=get_events&admin=1', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) throw new Error();
    _allEvents = data.events;
    renderEventsTable(_allEvents);
    updateEventsChips(_allEvents);
  } catch(e) {
    const tb = document.getElementById('eventsTableBody');
    if (tb) tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">Failed to load events.</td></tr>';
  }
}

function updateEventsChips(events) {
  const total    = document.getElementById('evtCountTotal');
  const active   = document.getElementById('evtCountActive');
  const specific = document.getElementById('evtCountSpecific');
  if (total)    total.textContent    = events.length;
  if (active)   active.textContent   = events.filter(e => parseInt(e.is_active)).length;
  if (specific) specific.textContent = events.filter(e => e.event_date).length;
}

function filterEventsTable() {
  const q       = (document.getElementById('evtSearch')?.value || '').toLowerCase();
  const session = document.getElementById('evtSessionFilter')?.value || '';
  const status  = document.getElementById('evtStatusFilter')?.value;

  const filtered = _allEvents.filter(e => {
    const matchQ = !q || e.event_name.toLowerCase().includes(q) || e.venue.toLowerCase().includes(q);
    const matchS = !session || e.session === session;
    const matchSt = status === '' || status === undefined || String(e.is_active) === status;
    return matchQ && matchS && matchSt;
  });
  renderEventsTable(filtered);
}

function renderEventsTable(events) {
  const tb = document.getElementById('eventsTableBody');
  if (!tb) return;

  if (!events.length) {
    tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">No events match your filters.</td></tr>';
    return;
  }

  tb.innerHTML = events.map(e => {
    const active     = parseInt(e.is_active);
    const sessionLbl = e.session === 'morning' ? '🌅 Morning' : '☀️ Afternoon';

    // FIX 2: detect expired specific-date events and show a red badge
    const todayStr   = new Date().toISOString().slice(0, 10);
    const isPastDate = e.event_date && e.event_date < todayStr;
    const dateLbl    = e.event_date
      ? `<span style="background:${isPastDate ? '#fee2e2' : '#fff3e0'};color:${isPastDate ? '#dc2626' : '#c0620a'};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;">
           ${escE(e.event_date)}${isPastDate ? ' ⚠ Expired' : ''}
         </span>`
      : `<span style="background:var(--green-pale);color:var(--green-dark);padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;">Every day</span>`;

    return `<tr>
      <td><strong>${escE(e.event_name)}</strong></td>
      <td>${sessionLbl}</td>
      <td style="font-weight:700;color:var(--green-dark);">${escE(e.event_time_fmt)}</td>
      <td>${escE(e.venue)}</td>
      <td>${dateLbl}</td>
      <td><span class="status-badge ${active ? 'approved' : 'rejected'}">${active ? 'Active' : 'Inactive'}</span></td>
      <td style="display:flex;gap:6px;flex-wrap:wrap;">
        <button class="btn-edit" onclick="openEditEventModal(${e.id})">Edit</button>
        <button class="btn-edit" style="background:${active ? '#fef3c7' : 'var(--green-pale)'};"
                onclick="toggleEvent(${e.id})">${active ? 'Deactivate' : 'Activate'}</button>
        <button class="btn-reject-sm" onclick="deleteEvent(${e.id})">Delete</button>
      </td>
    </tr>`;
  }).join('');
}

function escE(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Open modal for NEW event ── */
function openEventModal() {
  document.getElementById('emId').value      = '';
  document.getElementById('emName').value    = '';
  document.getElementById('emSession').value = 'morning';
  document.getElementById('emTime').value    = '';
  document.getElementById('emVenue').value   = '';
  document.getElementById('emDate').value    = '';
  document.getElementById('emActive').value  = '1';
  document.getElementById('emOrder').value   = '0';
  document.getElementById('eventModalTitle').textContent = 'Add New Event';
  document.getElementById('emBtnText').textContent       = 'Add Event';
  openModal('eventModal');
}

/* ── Open modal for EDIT ── */
function openEditEventModal(id) {
  const e = _allEvents.find(x => parseInt(x.id) === id);
  if (!e) return;
  document.getElementById('emId').value      = e.id;
  document.getElementById('emName').value    = e.event_name;
  document.getElementById('emSession').value = e.session;
  // time comes back as "HH:MM:SS" — input[type=time] needs "HH:MM"
  document.getElementById('emTime').value    = (e.event_time || '').substring(0,5);
  document.getElementById('emVenue').value   = e.venue;
  document.getElementById('emDate').value    = e.event_date || '';
  document.getElementById('emActive').value  = String(e.is_active);
  document.getElementById('emOrder').value   = e.sort_order;
  document.getElementById('eventModalTitle').textContent = 'Edit Event';
  document.getElementById('emBtnText').textContent       = 'Save Changes';
  openModal('eventModal');
}

/* ── Submit create / update ── */
async function submitEventModal() {
  const id    = document.getElementById('emId').value;
  const name  = document.getElementById('emName').value.trim();
  const time  = document.getElementById('emTime').value;
  if (!name || !time) { showToast('Event name and time are required.', 'error'); return; }

  const action  = id ? 'update_event' : 'create_event';
  const payload = {
    id:         id ? parseInt(id) : undefined,
    event_name: name,
    session:    document.getElementById('emSession').value,
    event_time: time,
    venue:      document.getElementById('emVenue').value.trim(),
    event_date: document.getElementById('emDate').value || null,
    is_active:  parseInt(document.getElementById('emActive').value),
    sort_order: parseInt(document.getElementById('emOrder').value) || 0,
  };

  const btn = document.getElementById('emBtnText');
  btn.textContent = 'Saving…';
  try {
    const res  = await fetch(`api/events.php?action=${action}`, {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) {
      closeModal('eventModal');
      showToast(id ? 'Event updated ✓' : 'Event added ✓');
      loadEvents();
    } else {
      showToast(data.message || 'Save failed.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.textContent = id ? 'Save Changes' : 'Add Event'; }
}

async function toggleEvent(id) {
  try {
    const res  = await fetch('api/events.php?action=toggle_event', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) { showToast('Status updated ✓'); loadEvents(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}

async function deleteEvent(id) {
  if (!confirm('Delete this event? This cannot be undone.')) return;
  try {
    const res  = await fetch('api/events.php?action=delete_event', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) { showToast('Event deleted'); loadEvents(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}

// ---- ATTRACTIONS (kept as stubs to avoid JS errors if referenced elsewhere) ----
function filterAttractions() {}
function editAttraction(id) {}
function removeAttraction(btn) {}
function openAttractionModal() { openEventModal(); }

// ---- MODALS ----
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}
function openPromoModal() { openModal('promoModal'); }
function goOverviewTicketing() { showPage('ticketing'); }
function goToPromotions() {
  showPage('ticketing');
  const btn = document.querySelector('.tab[onclick*="tab-promotions"]');
  if (btn) switchTab(btn, 'tab-promotions');
}
function openUploadModal() { openModal('uploadModal'); }

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) closeModal(overlay.id);
  });
});

// ---- TOAST ----
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = (type === 'error' ? '✕ ' : '✓ ') + message;
  toast.style.background = type === 'error' ? '#DC2626' : '#2D5A27';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

// ---- CHARTS ----
const CHART_COLORS = {
  labels:  ['Adult', 'Child', 'Family', 'Senior', 'Group'],
  colors:  ['#2D5A27', '#D4872A', '#C9541E', '#6B52A3', '#2563EB'],
  dotClass:['green',   'amber',   'orange',  'purple',  'blue'],
};

let _visitorChart  = null;
let _ticketChart   = null;
let _revenueChart  = null;
let _currentDays   = 7;

// ── Fetch chart data from PHP and refresh all charts ──
async function loadChartData(days) {
  days = days || _currentDays;
  _currentDays = days;
  try {
    const res  = await fetch(`api/tickets.php?action=chart_data&days=${days}`, { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;

    updateVisitorChart(data.visitor_labels, data.visitor_data);
    updateTicketChart(data.type_labels, data.type_counts);
    updateRevenueChart(data.revenue_labels, data.revenue_data);
    updateReportsStatCards(data);
  } catch(e) { console.error('loadChartData error', e); }
}

// ── Visitor Trend (line) ──
function updateVisitorChart(labels, data) {
  const ctx = document.getElementById('visitorChart')?.getContext('2d');
  if (!ctx) return;
  if (_visitorChart) {
    _visitorChart.data.labels        = labels;
    _visitorChart.data.datasets[0].data = data;
    _visitorChart.update();
    return;
  }
  _visitorChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Tickets Sold',
        data,
        borderColor: '#3E7A34',
        backgroundColor: 'rgba(90,158,78,0.1)',
        borderWidth: 2.5,
        pointRadius: 5,
        pointBackgroundColor: '#2D5A27',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#7A9170', font: { size: 12 }, precision: 0 } },
        x: { grid: { display: false }, ticks: { color: '#7A9170', font: { size: 12 } } }
      }
    }
  });
}

// ── Ticket Type Donut ──
function updateTicketChart(labels, counts) {
  const ctx = document.getElementById('ticketChart')?.getContext('2d');
  if (!ctx) return;

  // Map types to fixed color slots (Adult→green, Child→amber, etc.)
  const colorMap = {};
  CHART_COLORS.labels.forEach((l, i) => { colorMap[l] = { color: CHART_COLORS.colors[i], dot: CHART_COLORS.dotClass[i] }; });

  const bgColors = labels.map(l => colorMap[l]?.color || '#aaa');
  const total    = counts.reduce((a, b) => a + b, 0);

  if (_ticketChart) {
    _ticketChart.data.labels            = labels;
    _ticketChart.data.datasets[0].data  = counts;
    _ticketChart.data.datasets[0].backgroundColor = bgColors;
    _ticketChart.update();
  } else {
    _ticketChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data: counts, backgroundColor: bgColors, borderWidth: 3, borderColor: '#fff' }]
      },
      options: { responsive: true, cutout: '65%', plugins: { legend: { display: false } } }
    });
  }

  // Update legend
  const legendEl = document.getElementById('donutLegend');
  if (legendEl) {
    legendEl.innerHTML = labels.map((l, i) => {
      const pct  = total > 0 ? Math.round((counts[i] / total) * 100) : 0;
      const dot  = colorMap[l]?.dot || 'green';
      return `<div class="legend-item"><span class="legend-dot ${dot}"></span>${l} — ${pct}%</div>`;
    }).join('');
  }
}

// ── Monthly Revenue Bar ──
function updateRevenueChart(labels, data) {
  const ctx = document.getElementById('revenueChart')?.getContext('2d');
  if (!ctx) return;
  if (_revenueChart) {
    _revenueChart.data.labels           = labels;
    _revenueChart.data.datasets[0].data = data;
    _revenueChart.update();
    return;
  }
  _revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Revenue (RM)',
        data,
        backgroundColor: 'rgba(45,90,39,0.8)',
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#7A9170', font: { size: 12 }, callback: v => 'RM' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) } },
        x: { grid: { display: false }, ticks: { color: '#7A9170', font: { size: 12 } } }
      }
    }
  });
}

// ── Reports stat cards ──
function updateReportsStatCards(data) {
  const mv = document.getElementById('reportMonthVisitors');
  const mr = document.getElementById('reportMonthRevenue');
  const mt = document.getElementById('reportMonthTickets');
  if (mv) mv.textContent = (data.month_visitors || 0).toLocaleString();
  if (mr) mr.textContent = 'RM' + parseFloat(data.month_revenue || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  if (mt) mt.textContent = (data.month_tickets  || 0).toLocaleString();
  // FIX 3: Pull satisfaction rate live from real feedback data
  loadSatisfactionRate();
}

// FIX 3: Live satisfaction rate from feedback stats
async function loadSatisfactionRate() {
  try {
    const res  = await fetch('api/feedback.php?action=stats', { credentials: 'include' });
    const data = await res.json();
    const sr   = document.getElementById('reportSatisfactionRate');
    if (!sr) return;
    // feedback.php stats returns { avg, total, unread, pending, breakdown }
    const avg = parseFloat(data.avg || data.avg_rating || 0);
    if (!data.success || !avg) { sr.textContent = '—'; return; }
    const pct = Math.round((avg / 5) * 100);
    sr.textContent = pct + '%';
    sr.title = 'Based on ' + (data.total || 0) + ' reviews · Avg ' + avg.toFixed(1) + ' / 5 ★';
  } catch(e) {}
}

// ── Filter button handler ──
function setVisitorFilter(btn, days) {
  document.querySelectorAll('.chart-filters .filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadChartData(days);
}

// ── Called when overview page is shown ──
let overviewChartsInit = false;
function initOverviewCharts() {
  if (overviewChartsInit) return;
  overviewChartsInit = true;
  loadChartData(7);
}

// ── Called when reports page is shown ──
let reportsChartInit = false;
function initReportsChart() {
  if (reportsChartInit) return;
  reportsChartInit = true;
  // Re-use same data fetch; charts share instances
  loadChartData(_currentDays);
  // FIX 3: also pull satisfaction rate on first load of reports page
  loadSatisfactionRate();
}

// ── Auto-load media gallery when that page is shown ──
const _origShowPage = typeof showPage === 'function' ? showPage : null;
if (_origShowPage) {
  window.showPage = function(name) {
    _origShowPage(name);
    if (name === 'media') loadMediaGallery();
  };
}

// ── Drag-and-drop onto upload drop zone ──
document.addEventListener('DOMContentLoaded', () => {
  const dz = document.getElementById('uploadDropZone');
  if (dz) {
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--green-mid)'; });
    dz.addEventListener('dragleave', () => { dz.style.borderColor = ''; });
    dz.addEventListener('drop', e => {
      e.preventDefault(); dz.style.borderColor = '';
      const f = e.dataTransfer.files[0];
      if (f) {
        const dt = new DataTransfer(); dt.items.add(f);
        const inp = document.getElementById('umFileInput');
        inp.files = dt.files;
        previewFile(inp);
      }
    });
  }
});

async function loadAnnouncements() {
  try {
    const res  = await fetch('api/announcements.php?action=get_all_announcements', { credentials: 'include' });
    const data = await res.json();
    const list = document.getElementById('announceList');
    if (!list) return;
 
    if (!data.success || !data.announcements.length) {
      list.innerHTML = '<div style="text-align:center;padding:32px;color:var(--text-muted);">No announcements yet. Click "+ New Announcement" to add one.</div>';
      return;
    }
 
    const colorHex = { orange:'#f59e0b', green:'#22c55e', blue:'#3b82f6', purple:'#a855f7' };
 
    list.innerHTML = data.announcements.map(a => {
      const date   = new Date(a.created_at).toLocaleDateString('en-MY', { day:'numeric', month:'short', year:'numeric' });
      const active = parseInt(a.is_active);
      const dot    = colorHex[a.icon_color] || colorHex.orange;
      return `
        <div class="announce-item" id="ann-row-${a.id}">
          <div class="announce-icon ${a.icon_color}" style="font-size:20px;line-height:1;padding-top:4px;">${escA(a.icon)}</div>
          <div class="announce-body" style="flex:1;">
            <strong>${escA(a.title)}</strong>
            <p>${escA(a.body)}</p>
            <small>Posted: ${date} · ${escA(a.audience)} ·
              <span style="color:${active ? '#22c55e' : '#aaa'};font-weight:600;">
                ${active ? '● Live' : '○ Draft'}
              </span>
            </small>
          </div>
          <div style="display:flex;gap:8px;flex-shrink:0;">
            <button class="btn-edit" onclick="openEditAnnouncementModal(${a.id})">Edit</button>
            <button class="btn-edit" style="background:${active ? '#fef3c7' : 'var(--green-pale)'};"
                    onclick="toggleAnnouncement(${a.id})">
              ${active ? 'Set Draft' : 'Set Live'}
            </button>
            <button class="btn-reject-sm" onclick="deleteAnnouncement(${a.id})">Delete</button>
          </div>
        </div>`;
    }).join('');
  } catch(e) { console.error('loadAnnouncements', e); }
}
 
function escA(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
 
/* Open modal for NEW announcement */
function openNewAnnouncementModal() {
  document.getElementById('amId').value       = '';
  document.getElementById('amIcon').value     = '📢';
  document.getElementById('amColor').value    = 'orange';
  document.getElementById('amTitle').value    = '';
  document.getElementById('amBody').value     = '';
  document.getElementById('amAudience').value = 'All Visitors';
  document.getElementById('amActive').value   = '1';
  document.getElementById('announcementModalTitle').textContent = 'New Announcement';
  document.getElementById('amBtnText').textContent = 'Publish Announcement';
  openModal('announcementModal');
}
 
/* Open modal for EDIT */
async function openEditAnnouncementModal(id) {
  try {
    const res  = await fetch('api/announcements.php?action=get_all_announcements', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;
    const a = data.announcements.find(x => parseInt(x.id) === id);
    if (!a) return;
 
    document.getElementById('amId').value       = a.id;
    document.getElementById('amIcon').value     = a.icon;
    document.getElementById('amColor').value    = a.icon_color;
    document.getElementById('amTitle').value    = a.title;
    document.getElementById('amBody').value     = a.body;
    document.getElementById('amAudience').value = a.audience;
    document.getElementById('amActive').value   = a.is_active;
    document.getElementById('announcementModalTitle').textContent = 'Edit Announcement';
    document.getElementById('amBtnText').textContent = 'Save Changes';
    openModal('announcementModal');
  } catch(e) { showToast('Failed to load announcement', 'error'); }
}
 
/* Submit create / update */
async function submitAnnouncementModal() {
  const id    = document.getElementById('amId').value;
  const title = document.getElementById('amTitle').value.trim();
  const body  = document.getElementById('amBody').value.trim();
  if (!title || !body) { showToast('Title and message are required.', 'error'); return; }
 
  const action  = id ? 'update_announcement' : 'create_announcement';
  const payload = {
    id:         id ? parseInt(id) : undefined,
    icon:       document.getElementById('amIcon').value,
    icon_color: document.getElementById('amColor').value,
    title,
    body,
    audience:   document.getElementById('amAudience').value,
    is_active:  parseInt(document.getElementById('amActive').value),
  };
 
  const btn = document.getElementById('amBtnText');
  btn.textContent = 'Saving…';
  try {
    const res  = await fetch(`api/announcements.php?action=${action}`, {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) {
      closeModal('announcementModal');
      showToast(id ? 'Announcement updated ✓' : 'Announcement published ✓');
      loadAnnouncements();
    } else {
      showToast(data.message || 'Save failed.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.textContent = id ? 'Save Changes' : 'Publish Announcement'; }
}
 
async function toggleAnnouncement(id) {
  try {
    const res  = await fetch('api/announcements.php?action=toggle_announcement', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) { showToast('Status updated ✓'); loadAnnouncements(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}
 
async function deleteAnnouncement(id) {
  if (!confirm('Delete this announcement? This cannot be undone.')) return;
  try {
    const res  = await fetch('api/announcements.php?action=delete_announcement', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) { showToast('Announcement deleted'); loadAnnouncements(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}
 
/* ════════════════════════════════════════════════════════════════
   ZOO SETTINGS (opening hours)  –  admin-side JS
════════════════════════════════════════════════════════════════ */
 
async function loadZooSettings() {
  try {
    const res  = await fetch('api/announcements.php?action=get_settings', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;
    const s = data.settings;
    const ot = document.getElementById('settingOpenTime');
    const ct = document.getElementById('settingCloseTime');
    const le = document.getElementById('settingLastEntry');
    const op = document.getElementById('settingOnlinePurchase');
    if (ot) ot.value = s.open_time                  || '09:00';
    if (ct) ct.value = s.close_time                 || '18:00';
    if (le) le.value = s.last_entry_mins             || '60';
    if (op) op.value = s.last_online_purchase_mins   || '180';
  } catch(e) { console.error('loadZooSettings', e); }
}
 
async function saveZooSettings() {
  const payload = {
    settings: {
      open_time:                 document.getElementById('settingOpenTime').value,
      close_time:                document.getElementById('settingCloseTime').value,
      last_entry_mins:           document.getElementById('settingLastEntry').value,
      last_online_purchase_mins: document.getElementById('settingOnlinePurchase').value,
    }
  };
  try {
    const res  = await fetch('api/announcements.php?action=save_settings', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) showToast('Opening hours saved ✓');
    else showToast(data.message || 'Save failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}
 
/* ── Auto-load when these pages become visible ─────────────────── */
// Patch into the existing showPage function
(function() {
  const _orig = window.showPage;
  window.showPage = function(name) {
    if (typeof _orig === 'function') _orig(name);
    if (name === 'announcements') loadAnnouncements();
    if (name === 'settings')      loadZooSettings();
    if (name === 'events')        loadEvents();
  };
})();
 
// Also load on DOMContentLoaded if these pages are already active
document.addEventListener('DOMContentLoaded', function() {
  const activePage = document.querySelector('.page.active');
  if (!activePage) return;
  if (activePage.id === 'page-announcements') loadAnnouncements();
  if (activePage.id === 'page-settings')      loadZooSettings();
  if (activePage.id === 'page-events')        loadEvents();
});

/* ── Staff Management ──────────────────────────────────────────── */
 
async function loadStaff() {
  try {
    const res  = await fetch('api/staff.php?action=get_staff', { credentials: 'include' });
    const data = await res.json();
    const tbody = document.getElementById('staffTableBody');
    if (!data.success || !data.staff.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">No staff accounts yet.</td></tr>';
      return;
    }
    tbody.innerHTML = data.staff.map(s => {
      const initials = (s.username || s.full_name || '??').split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
      const active   = parseInt(s.is_active);
      const pending  = parseInt(s.must_change_pw);
      const joined   = s.created_at ? new Date(s.created_at).toLocaleDateString('en-MY', { day:'numeric', month:'short', year:'numeric' }) : '—';
      const roleLabel = s.role === 'admin' ? 'Admin' : 'Worker';
      const roleBg    = s.role === 'admin' ? 'var(--purple-light)' : 'var(--green-pale)';
      const roleColor = s.role === 'admin' ? 'var(--purple)' : 'var(--green-dark)';
      return `
        <tr id="staff-row-${s.user_id}">
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div class="user-avatar" style="width:36px;height:36px;font-size:12px;background:var(--green-dark);">${initials}</div>
              <div>
                <strong>${escA(s.username || s.full_name)}</strong>
                ${pending ? '<span style="font-size:10px;background:#FEF3C7;color:#92400E;padding:1px 6px;border-radius:4px;margin-left:4px;">Temp PW</span>' : ''}
                <br/><small style="color:var(--text-muted)">${escA(s.email)}</small>
              </div>
            </div>
          </td>
          <td><span style="font-size:12px;font-weight:600;padding:3px 8px;border-radius:6px;background:${roleBg};color:${roleColor};">${roleLabel}</span></td>
          <td>${escA(s.position || '—')}</td>
          <td><span class="status-badge ${active ? 'approved' : 'rejected'}">${active ? 'Active' : 'Inactive'}</span></td>
          <td>${joined}</td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
              <button class="btn-edit" onclick="openEditStaffModal(${s.user_id})">Edit</button>
              <button class="btn-edit" onclick="openResetPwModal(${s.user_id}, '${escA(s.username || s.full_name)}')">Reset PW</button>
              <button class="btn-edit" style="background:${active ? '#fef3c7' : 'var(--green-pale)'};"
                      onclick="toggleStaffStatus(${s.user_id})">${active ? 'Deactivate' : 'Activate'}</button>
              <button class="btn-reject-sm" onclick="deleteStaff(${s.user_id})">Delete</button>
            </div>
          </td>
        </tr>`;
    }).join('');
  } catch(e) { console.error('loadStaff', e); }
}
 
function openAddStaffModal() {
  document.getElementById('smUserId').value   = '';
  document.getElementById('smName').value     = '';
  document.getElementById('smEmail').value    = '';
  document.getElementById('smRole').value     = 'worker';
  document.getElementById('smPosition').value = '';
  document.getElementById('smPhone').value    = '';
  document.getElementById('smTempPw').value   = '';
  document.getElementById('smEmail').disabled = false;
  document.getElementById('smPwGroup').style.display = '';
  document.getElementById('staffModalTitle').textContent = 'Add Staff Member';
  document.getElementById('smBtnText').textContent = 'Create Account';
  openModal('staffModal');
}
 
async function openEditStaffModal(userId) {
  try {
    const res  = await fetch('api/staff.php?action=get_staff', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;
    const s = data.staff.find(x => parseInt(x.user_id) === userId);
    if (!s) return;
 
    document.getElementById('smUserId').value   = s.user_id;
    document.getElementById('smName').value     = s.username || s.full_name || '';
    document.getElementById('smEmail').value    = s.email || '';
    document.getElementById('smEmail').disabled = true; // don't change email on edit
    document.getElementById('smRole').value     = s.role;
    document.getElementById('smPosition').value = s.position || '';
    document.getElementById('smPhone').value    = s.phone || '';
    document.getElementById('smPwGroup').style.display = 'none'; // no pw field on edit
    document.getElementById('staffModalTitle').textContent = 'Edit Staff Member';
    document.getElementById('smBtnText').textContent = 'Save Changes';
    openModal('staffModal');
  } catch(e) { showToast('Failed to load staff data.', 'error'); }
}
 
async function submitStaffModal() {
  const userId  = document.getElementById('smUserId').value;
  const isEdit  = !!userId;
  const name    = document.getElementById('smName').value.trim();
  const email   = document.getElementById('smEmail').value.trim();
  const role    = document.getElementById('smRole').value;
  const position= document.getElementById('smPosition').value.trim();
  const phone   = document.getElementById('smPhone').value.trim();
  const tempPw  = document.getElementById('smTempPw').value;
 
  if (!name) { showToast('Full name is required.', 'error'); return; }
  if (!isEdit && !email) { showToast('Email is required.', 'error'); return; }
  if (!isEdit && tempPw.length < 6) { showToast('Temporary password must be at least 6 characters.', 'error'); return; }
 
  const btn = document.getElementById('smBtnText');
  btn.textContent = 'Saving…';
 
  try {
    const action  = isEdit ? 'update_staff' : 'create_staff';
    const payload = isEdit
      ? { user_id: parseInt(userId), name, role, position, phone }
      : { name, email, role, position, phone, temp_password: tempPw };
 
    const res  = await fetch(`api/staff.php?action=${action}`, {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) {
      closeModal('staffModal');
      showToast(isEdit ? 'Staff updated ✓' : 'Staff account created ✓');
      loadStaff();
    } else {
      showToast(data.message || 'Failed.', 'error');
    }
  } catch(e) { showToast('Network error.', 'error'); }
  finally { btn.textContent = isEdit ? 'Save Changes' : 'Create Account'; }
}
 
function openResetPwModal(userId, name) {
  document.getElementById('rpUserId').value    = userId;
  document.getElementById('rpStaffName').textContent = name;
  document.getElementById('rpTempPw').value    = '';
  openModal('resetPwModal');
}
 
async function submitResetPassword() {
  const userId = document.getElementById('rpUserId').value;
  const tempPw = document.getElementById('rpTempPw').value;
  if (tempPw.length < 6) { showToast('Password must be at least 6 characters.', 'error'); return; }
  try {
    const res  = await fetch('api/staff.php?action=reset_password', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: parseInt(userId), temp_password: tempPw }),
    });
    const data = await res.json();
    if (data.success) { closeModal('resetPwModal'); showToast('Password reset ✓'); loadStaff(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}
 
async function toggleStaffStatus(userId) {
  try {
    const res  = await fetch('api/staff.php?action=toggle_status', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId }),
    });
    const data = await res.json();
    if (data.success) { showToast('Status updated ✓'); loadStaff(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}
 
async function deleteStaff(userId) {
  if (!confirm('Delete this staff account? This cannot be undone.')) return;
  try {
    const res  = await fetch('api/staff.php?action=delete_staff', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId }),
    });
    const data = await res.json();
    if (data.success) { showToast('Staff deleted'); loadStaff(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch(e) { showToast('Network error.', 'error'); }
}
 
/* ── Patch showPage to auto-load staff list ─────────────────────── */
(function() {
  const _orig = window.showPage;
  window.showPage = function(name) {
    if (typeof _orig === 'function') _orig(name);
    if (name === 'staff') loadStaff();
  };
})();
 
document.addEventListener('DOMContentLoaded', function() {
  const activePage = document.querySelector('.page.active');
  if (activePage && activePage.id === 'page-staff') loadStaff();
});

let fbPage = 1;
let fbTotalPages = 1;
let fbSearchTimer = null;

function debounceFbSearch() {
  clearTimeout(fbSearchTimer);
  fbSearchTimer = setTimeout(() => { fbPage = 1; loadFeedback(); }, 350);
}
function changeFbPage(dir) {
  fbPage = Math.max(1, Math.min(fbTotalPages, fbPage + dir));
  loadFeedback();
}

/* ── Stats ───────────────────────────────────────────────────── */
async function loadFeedbackStats() {
  try {
    const res  = await fetch('api/feedback.php?action=stats', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;

    const avgEl = document.getElementById('fbAvgRating');
    if (avgEl) avgEl.textContent = data.avg || '—';

    const starsEl = document.getElementById('fbAvgStars');
    if (starsEl) {
      const full = Math.round(data.avg || 0);
      starsEl.textContent = '★'.repeat(full) + '☆'.repeat(5 - full);
    }

    const totalEl = document.getElementById('fbTotalLabel');
    if (totalEl) totalEl.textContent = 'Based on ' + data.total + ' review' + (data.total !== 1 ? 's' : '');

    const unreadEl = document.getElementById('fbUnreadCount');
    if (unreadEl) unreadEl.textContent = data.unread;

    // "Awaiting Reply" = only genuinely pending (not replied, not flagged)
    const pendingEl = document.getElementById('fbPendingCount');
    if (pendingEl) pendingEl.textContent = data.pending;

    const bd = data.breakdown || {};
    for (let r = 1; r <= 5; r++) {
      const pct = data.total ? Math.round(((bd[r] || 0) / data.total) * 100) : 0;
      const bar = document.getElementById('fbBar' + r);
      const lbl = document.getElementById('fbPct' + r);
      if (bar) bar.style.width = pct + '%';
      if (lbl) lbl.textContent = pct + '%';
    }
  } catch (e) { console.error('loadFeedbackStats', e); }
}

/* ── List ────────────────────────────────────────────────────── */
async function loadFeedback() {
  await loadFeedbackStats();

  const search = (document.getElementById('fbSearch') || {}).value || '';
  const rating = (document.getElementById('fbRatingFilter') || {}).value || '';
  const status = (document.getElementById('fbStatusFilter') || {}).value || '';

  const params = new URLSearchParams({ action: 'list', page: fbPage });
  if (search.trim()) params.set('search', search.trim());
  if (rating)        params.set('rating', rating);
  if (status)        params.set('status', status);

  const list = document.getElementById('fbList');
  if (!list) return;
  list.innerHTML = '<div style="text-align:center;padding:32px;color:var(--text-muted);">Loading…</div>';

  try {
    const res  = await fetch('api/feedback.php?' + params, { credentials: 'include' });
    const data = await res.json();

    if (!data.success) {
      list.innerHTML = '<div style="text-align:center;padding:32px;color:var(--text-muted);">Failed to load feedback.</div>';
      return;
    }

    fbTotalPages = data.total_pages || 1;
    const rows   = data.feedback   || [];

    if (!rows.length) {
      list.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);">No feedback found.</div>';
      const pag = document.getElementById('fbPagination');
      if (pag) pag.style.display = 'none';
      loadContactInfoTable();
      return;
    }

    // Mark unread silently
    const unreadIds = rows.filter(r => !r.is_read).map(r => r.id);
    if (unreadIds.length) {
      fetch('api/feedback.php?action=mark_read', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: unreadIds }),
      });
    }

    list.innerHTML = rows.map(fb => {
      const initials = (fb.name || '??').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
      const stars    = '★'.repeat(fb.rating) + '☆'.repeat(5 - fb.rating);
      const date     = new Date(fb.created_at).toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' });
      const dot      = !fb.is_read
        ? '<span style="width:8px;height:8px;border-radius:50%;background:var(--orange);display:inline-block;margin-left:6px;vertical-align:middle;"></span>'
        : '';

      const badgeMap = {
        pending: '<span style="background:#FEF3C7;color:#92400E;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;margin-left:6px;">Pending</span>',
        replied: '<span style="background:var(--green-pale);color:var(--green-dark);font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;margin-left:6px;">Replied</span>',
        flagged: '<span style="background:#FEE2E2;color:var(--red);font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;margin-left:6px;">Flagged</span>',
      };
      const badge = badgeMap[fb.status] || '';

      // Admin reply section with delete-reply button
      const replySection = fb.admin_reply
        ? `<div style="margin-top:12px;background:#f0f7ef;border-left:3px solid var(--green-light);padding:10px 14px;border-radius:0 8px 8px 0;font-size:13px;">
             <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
               <span>
                 <strong style="color:var(--green-dark);">Admin Reply</strong>
                 <span style="font-size:11px;color:var(--text-muted);margin-left:8px;">${new Date(fb.replied_at).toLocaleDateString('en-MY',{day:'numeric',month:'short',year:'numeric'})}</span>
               </span>
               <button onclick="deleteFbReply(${fb.id})"
                 style="background:none;border:1px solid #FECACA;color:var(--red);font-size:11px;font-weight:600;padding:3px 10px;border-radius:6px;cursor:pointer;">
                 ✕ Delete Reply
               </button>
             </div>
             <p style="margin:0;color:var(--text-mid);line-height:1.6;">${escA(fb.admin_reply)}</p>
           </div>`
        : '';

      // Action buttons
      const replyBtn  = `<button class="btn-approve" onclick="openFbReply(${fb.id})">&#x21A9; Reply</button>`;
      const flagBtn   = fb.status !== 'flagged'
        ? `<button class="btn-reject" onclick="flagFeedback(${fb.id})">&#x2691; Flag</button>`
        : `<button class="btn-edit"   onclick="unflagFeedback(${fb.id})">&#x21BA; Unflag</button>`;
      const deleteBtn = `<button class="btn-reject-sm" title="Delete feedback" onclick="deleteFeedback(${fb.id})">&#x1F5D1;</button>`;

      return `
        <div class="review-card" id="fb-row-${fb.id}" style="${!fb.is_read ? 'border-left:3px solid var(--orange);' : ''}">
          <div class="review-top">
            <div class="reviewer-avatar">${initials}</div>
            <div>
              <div class="reviewer-name">${escA(fb.name)}${dot}${badge}</div>
              <div style="font-size:12px;color:var(--text-muted);">${escA(fb.email)}</div>
              <div class="review-stars" style="margin-top:4px;">${stars} <span>${date}</span></div>
            </div>
            <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start;">
              ${replyBtn}
              ${flagBtn}
              ${deleteBtn}
            </div>
          </div>
          <p class="review-text">${escA(fb.message)}</p>
          ${replySection}
        </div>`;
    }).join('');

    // Pagination
    const pag = document.getElementById('fbPagination');
    if (pag) {
      pag.style.display = 'flex';
      const info = document.getElementById('fbPaginationInfo');
      if (info) info.textContent = 'Page ' + fbPage + ' of ' + fbTotalPages + '  (' + data.total + ' total)';
      const prev = document.getElementById('fbPrevBtn');
      const next = document.getElementById('fbNextBtn');
      if (prev) prev.disabled = fbPage <= 1;
      if (next) next.disabled = fbPage >= fbTotalPages;
    }

    loadContactInfoTable();
  } catch (e) { console.error('loadFeedback', e); }
}

/* ── Open reply modal ────────────────────────────────────────── */
function openFbReply(id) {
  document.getElementById('fbReplyId').value   = id;
  document.getElementById('fbReplyText').value = '';

  const row     = document.getElementById('fb-row-' + id);
  const preview = document.getElementById('fbOriginalPreview');
  if (row && preview) {
    const nameEl  = row.querySelector('.reviewer-name');
    const starsEl = row.querySelector('.review-stars');
    const msgEl   = row.querySelector('.review-text');
    const name    = nameEl  ? nameEl.textContent.trim()  : '';
    const stars   = starsEl ? starsEl.textContent.trim() : '';
    const msg     = msgEl   ? msgEl.textContent.trim()   : '';
    preview.innerHTML =
      '<strong style="font-size:13px;color:var(--text-dark);">'  + escA(name)  + '</strong>' +
      '<span style="font-size:12px;color:var(--text-muted);margin-left:8px;">' + escA(stars) + '</span>' +
      '<p style="margin:8px 0 0;font-size:13px;color:var(--text-mid);line-height:1.6;">' + escA(msg) + '</p>';
  }
  openModal('fbReplyModal');
}

/* ── Submit reply ────────────────────────────────────────────── */
async function submitFbReply() {
  const id    = parseInt(document.getElementById('fbReplyId').value);
  const reply = document.getElementById('fbReplyText').value.trim();
  if (!reply) { showToast('Reply cannot be empty.', 'error'); return; }
  try {
    const res  = await fetch('api/feedback.php?action=reply', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, reply }),
    });
    const data = await res.json();
    if (data.success) { closeModal('fbReplyModal'); showToast('Reply sent ✓'); loadFeedback(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch (e) { showToast('Network error.', 'error'); }
}

/* ── Delete reply ────────────────────────────────────────────── */
async function deleteFbReply(id) {
  if (!confirm('Delete this reply? The feedback will go back to Pending status.')) return;
  try {
    const res  = await fetch('api/feedback.php?action=delete_reply', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) { showToast('Reply deleted'); loadFeedback(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch (e) { showToast('Network error.', 'error'); }
}

/* ── Flag / Unflag / Delete whole feedback ───────────────────── */
async function flagFeedback(id) {
  const res  = await fetch('api/feedback.php?action=flag', {
    method: 'POST', credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Flagged'); loadFeedback(); }
  else showToast(data.message || 'Failed.', 'error');
}

async function unflagFeedback(id) {
  const res  = await fetch('api/feedback.php?action=unflag', {
    method: 'POST', credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Unflagged'); loadFeedback(); }
  else showToast(data.message || 'Failed.', 'error');
}

async function deleteFeedback(id) {
  if (!confirm('Delete this feedback entirely? This cannot be undone.')) return;
  const res  = await fetch('api/feedback.php?action=delete', {
    method: 'POST', credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Deleted'); loadFeedback(); }
  else showToast(data.message || 'Failed.', 'error');
}

/* ── Contact Info table ──────────────────────────────────────── */
async function loadContactInfoTable() {
  const tbody = document.getElementById('contactInfoBody');
  if (!tbody) return;
  try {
    const res  = await fetch('api/contact_info.php?action=list', { credentials: 'include' });
    const data = await res.json();
    const rows = data.contacts || [];
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No contact cards yet. Click "+ Add Contact Card" to create one.</td></tr>';
      return;
    }
    tbody.innerHTML = rows.map(c => `
      <tr>
        <td style="font-size:22px;">${c.icon}</td>
        <td><strong>${escA(c.department)}</strong></td>
        <td>${c.phone ? escA(c.phone) : '<span style="color:var(--text-muted)">—</span>'}</td>
        <td>${c.email ? '<a href="mailto:' + escA(c.email) + '" style="color:var(--green-dark);">' + escA(c.email) + '</a>' : '<span style="color:var(--text-muted)">—</span>'}</td>
        <td><span class="status-badge ${parseInt(c.is_active) ? 'approved' : 'rejected'}">${parseInt(c.is_active) ? 'Visible' : 'Hidden'}</span></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="btn-edit"      onclick="openEditContactModal(${c.id})">Edit</button>
            <button class="btn-edit"      onclick="toggleContact(${c.id})">${parseInt(c.is_active) ? 'Hide' : 'Show'}</button>
            <button class="btn-reject-sm" onclick="deleteContact(${c.id})">Delete</button>
          </div>
        </td>
      </tr>`).join('');
  } catch (e) { console.error('loadContactInfoTable', e); }
}

function openContactModal() {
  document.getElementById('cmId').value    = '';
  document.getElementById('cmIcon').value  = '📞';
  document.getElementById('cmDept').value  = '';
  document.getElementById('cmPhone').value = '';
  document.getElementById('cmEmail').value = '';
  document.getElementById('cmSort').value  = '0';
  document.getElementById('contactModalTitle').textContent = 'Add Contact Card';
  document.getElementById('cmBtnText').textContent         = 'Create Card';
  openModal('contactModal');
}

async function openEditContactModal(id) {
  try {
    const res  = await fetch('api/contact_info.php?action=list', { credentials: 'include' });
    const data = await res.json();
    const c    = (data.contacts || []).find(x => parseInt(x.id) === id);
    if (!c) return;
    document.getElementById('cmId').value    = c.id;
    document.getElementById('cmIcon').value  = c.icon;
    document.getElementById('cmDept').value  = c.department;
    document.getElementById('cmPhone').value = c.phone || '';
    document.getElementById('cmEmail').value = c.email || '';
    document.getElementById('cmSort').value  = c.sort_order;
    document.getElementById('contactModalTitle').textContent = 'Edit Contact Card';
    document.getElementById('cmBtnText').textContent         = 'Save Changes';
    openModal('contactModal');
  } catch (e) { showToast('Failed to load.', 'error'); }
}

async function submitContactModal() {
  const id   = document.getElementById('cmId').value;
  const dept = document.getElementById('cmDept').value.trim();
  if (!dept) { showToast('Department name is required.', 'error'); return; }
  const payload = {
    icon:       document.getElementById('cmIcon').value.trim()  || '📞',
    department: dept,
    phone:      document.getElementById('cmPhone').value.trim(),
    email:      document.getElementById('cmEmail').value.trim(),
    sort_order: parseInt(document.getElementById('cmSort').value) || 0,
  };
  if (id) payload.id = parseInt(id);
  const act = id ? 'update' : 'create';
  try {
    const res  = await fetch('api/contact_info.php?action=' + act, {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) { closeModal('contactModal'); showToast(id ? 'Updated ✓' : 'Created ✓'); loadContactInfoTable(); }
    else showToast(data.message || 'Failed.', 'error');
  } catch (e) { showToast('Network error.', 'error'); }
}

async function toggleContact(id) {
  const res  = await fetch('api/contact_info.php?action=toggle', {
    method: 'POST', credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Visibility updated ✓'); loadContactInfoTable(); }
  else showToast(data.message || 'Failed.', 'error');
}

async function deleteContact(id) {
  if (!confirm('Delete this contact card?')) return;
  const res  = await fetch('api/contact_info.php?action=delete', {
    method: 'POST', credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Deleted'); loadContactInfoTable(); }
  else showToast(data.message || 'Failed.', 'error');
}

/* ── Patch showPage ──────────────────────────────────────────── */
(function() {
  const _orig = window.showPage;
  window.showPage = function(name) {
    if (typeof _orig === 'function') _orig(name);
    if (name === 'feedback') { fbPage = 1; loadFeedback(); }
  };
})();

document.addEventListener('DOMContentLoaded', function() {
  const active = document.querySelector('.page.active');
  if (active && active.id === 'page-feedback') { fbPage = 1; loadFeedback(); }
});

/* ── Sidebar badge ───────────────────────────────────────────── */
async function refreshFeedbackBadge() {
  try {
    const res  = await fetch('api/feedback.php?action=stats', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;
    const unread  = data.unread || 0;
    const navItem = document.querySelector('.nav-item[data-page="feedback"]');
    if (!navItem) return;
    let badge = navItem.querySelector('.nav-badge');
    if (unread > 0) {
      if (!badge) { badge = document.createElement('span'); badge.className = 'nav-badge'; navItem.appendChild(badge); }
      badge.textContent = unread;
    } else if (badge) {
      badge.remove();
    }
  } catch (e) {}
}
refreshFeedbackBadge();
setInterval(refreshFeedbackBadge, 30000);

/* ════════════════════════════════════════════════════════
   HELPER — HTML escape (used in notification rendering)
════════════════════════════════════════════════════════ */
function escA(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ════════════════════════════════════════════════════════
   EXPORT OVERVIEW REPORT  (CSV download)
════════════════════════════════════════════════════════ */

async function exportOverviewReport() {
  try {
    showToast('Preparing report…');
    const [statsRes, paymentsRes] = await Promise.all([
      fetch('api/tickets.php?action=stats',       { credentials: 'include' }),
      fetch('api/tickets.php?action=get_pending',  { credentials: 'include' }),
    ]);
    const stats    = await statsRes.json();
    const payments = await paymentsRes.json();

    const now     = new Date().toLocaleString('en-MY');
    const rows    = [
      ['WildTrack Zoo — Overview Report'],
      ['Generated', now],
      [],
      ['SUMMARY'],
      ['Total Tickets Sold', stats.total_tickets ?? '—'],
      ['Total Revenue (RM)', stats.total_revenue  ?? '—'],
      ['Pending Approvals',  stats.pending_count  ?? '—'],
      ["Today's Tickets",    stats.today_tickets  ?? '—'],
      [],
      ['RECENT BOOKINGS'],
      ['TXN ID','Customer','Email','Tickets','Amount (RM)','Visit Date','Status','Approved By'],
    ];

    (payments.payments || []).forEach(p => {
      rows.push([
        p.booking_ref,
        p.visitor_name  || p.username || '',
        p.visitor_email || p.email    || '',
        p.ticket_types  || '',
        parseFloat(p.final_total || p.total_price || 0).toFixed(2),
        '"' + (p.visit_date || '') + '"',
        p.status        || '',
        p.approved_by_name || '—',
      ]);
    });

    const csv = rows.map(r =>
      r.map(cell => '"' + String(cell ?? '').replace(/"/g, '""') + '"').join(',')
    ).join('\n');
    
    const BOM = '\uFEFF';
    const blob = new Blob([BOM + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'wildtrack_overview_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
    showToast('Report downloaded ✓');
  } catch(e) { showToast('Export failed. Try again.', 'error'); }
}

/* ════════════════════════════════════════════════════════
   EXPORT REPORTS PDF  (print-to-PDF via browser)
   Gathers live data, opens a clean print window
════════════════════════════════════════════════════════ */
async function exportReportsPDF() {
  try {
    showToast('Building PDF…');
    const [statsRes, chartRes, fbRes] = await Promise.all([
      fetch('api/tickets.php?action=stats',            { credentials: 'include' }),
      fetch('api/tickets.php?action=chart_data&days=30', { credentials: 'include' }),
      fetch('api/feedback.php?action=stats',           { credentials: 'include' }),
    ]);
    const stats = await statsRes.json();
    const chart = await chartRes.json();
    const fb    = await fbRes.json();

    const satRate = fb.success && fb.avg
      ? Math.round((parseFloat(fb.avg) / 5) * 100) + '% (' + parseFloat(fb.avg).toFixed(1) + '/5)'
      : '—';

    const revenueRows = (chart.revenue_labels || []).map((lbl, i) =>
      `<tr><td>${lbl}</td><td>RM ${parseFloat(chart.revenue_data?.[i] || 0).toFixed(2)}</td></tr>`
    ).join('');

    const html = `<!DOCTYPE html><html><head>
      <meta charset="UTF-8">
      <title>WildTrack Reports — ${new Date().toLocaleDateString('en-MY')}</title>
      <style>
        body { font-family: 'Segoe UI', sans-serif; color:#1a2b18; padding:40px; font-size:13px; }
        h1 { color:#2D5A27; font-size:22px; margin-bottom:4px; }
        h2 { color:#2D5A27; font-size:15px; margin:28px 0 10px; border-bottom:2px solid #eaf1e8; padding-bottom:6px; }
        .meta { color:#7a9170; font-size:12px; margin-bottom:28px; }
        .grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px; margin-bottom:24px; }
        .card { background:#f2f5f0; border-radius:10px; padding:16px 20px; }
        .card .val { font-size:22px; font-weight:700; color:#2D5A27; }
        .card .lbl { font-size:12px; color:#7a9170; margin-top:4px; }
        table { width:100%; border-collapse:collapse; }
        th { background:#2D5A27; color:#fff; padding:8px 12px; text-align:left; font-size:12px; }
        td { padding:8px 12px; border-bottom:1px solid #eaf1e8; }
        tr:last-child td { border-bottom:none; }
        @media print { body { padding:20px; } }
      </style>
    </head><body>
      <h1>WildTrack Zoo — Analytics Report</h1>
      <div class="meta">Generated: ${new Date().toLocaleString('en-MY')} · Period: Last 30 days</div>

      <h2>Key Metrics</h2>
      <div class="grid">
        <div class="card"><div class="val">${(stats.month_visitors || chart.month_visitors || 0).toLocaleString()}</div><div class="lbl">Monthly Visitors</div></div>
        <div class="card"><div class="val">RM${parseFloat(stats.total_revenue||0).toLocaleString('en-MY',{minimumFractionDigits:2})}</div><div class="lbl">Total Revenue (All Time)</div></div>
        <div class="card"><div class="val">${satRate}</div><div class="lbl">Satisfaction Rate</div></div>
        <div class="card"><div class="val">${(stats.total_tickets||0).toLocaleString()}</div><div class="lbl">Total Tickets Sold</div></div>
      </div>

      <h2>Monthly Revenue Breakdown</h2>
      <table>
        <tr><th>Month</th><th>Revenue (RM)</th></tr>
        ${revenueRows || '<tr><td colspan="2" style="color:#aaa;">No data</td></tr>'}
      </table>

      <h2>Ticket Type Distribution</h2>
      <table>
        <tr><th>Type</th><th>Count</th></tr>
        ${(chart.type_labels||[]).map((lbl,i)=>`<tr><td>${lbl}</td><td>${chart.type_counts?.[i]??0}</td></tr>`).join('')||'<tr><td colspan="2" style="color:#aaa;">No data</td></tr>'}
      </table>

      <h2>Feedback Summary</h2>
      <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Total Reviews</td><td>${fb.total||0}</td></tr>
        <tr><td>Average Rating</td><td>${fb.avg ? parseFloat(fb.avg).toFixed(1)+' / 5' : '—'}</td></tr>
        <tr><td>Awaiting Reply</td><td>${fb.pending||0}</td></tr>
        <tr><td>5 ★ Reviews</td><td>${fb.breakdown?.[5]||0}</td></tr>
        <tr><td>4 ★ Reviews</td><td>${fb.breakdown?.[4]||0}</td></tr>
        <tr><td>3 ★ or below</td><td>${(fb.breakdown?.[3]||0)+(fb.breakdown?.[2]||0)+(fb.breakdown?.[1]||0)}</td></tr>
      </table>
    </body></html>`;

    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.onload = () => { win.focus(); win.print(); };
    showToast('PDF ready — use your browser\'s Print → Save as PDF ✓');
  } catch(e) { showToast('PDF export failed. Try again.', 'error'); }
}

/* ════════════════════════════════════════════════════════
   GLOBAL SEARCH  — searches bookings, staff, events
════════════════════════════════════════════════════════ */
let _searchDebounce = null;

function handleGlobalSearch(q) {
  const results = document.getElementById('globalSearchResults');
  if (!results) return;
  q = (q || '').trim().toLowerCase();
  if (q.length < 2) { results.style.display = 'none'; return; }

  clearTimeout(_searchDebounce);
  _searchDebounce = setTimeout(async () => {
    const matches = [];

    // Search bookings (in-memory _allPayments)
    (_allPayments || []).forEach(p => {
      const ref   = (p.booking_ref  || '').toLowerCase();
      const name  = (p.visitor_name || p.username || '').toLowerCase();
      const email = (p.visitor_email|| p.email    || '').toLowerCase();
      if (ref.includes(q) || name.includes(q) || email.includes(q)) {
        matches.push({
          icon:    '🎫',
          title:   p.booking_ref,
          sub:     (p.visitor_name || p.username || '') + ' · RM' + parseFloat(p.final_total||p.total_price||0).toFixed(2),
          badge:   p.status,
          action:  () => { showPage('ticketing'); document.getElementById('approvalSearch').value = p.booking_ref; filterApprovalTable(); },
        });
      }
    });

    // Search events (in-memory _allEvents)
    (_allEvents || []).forEach(e => {
      if ((e.event_name||'').toLowerCase().includes(q) || (e.venue||'').toLowerCase().includes(q)) {
        matches.push({
          icon:   '📅',
          title:  e.event_name,
          sub:    e.venue + ' · ' + (e.session==='morning'?'🌅 Morning':'☀️ Afternoon'),
          badge:  parseInt(e.is_active) ? 'active' : 'inactive',
          action: () => showPage('events'),
        });
      }
    });

    if (!matches.length) {
      results.innerHTML = '<div style="padding:16px 18px;font-size:13px;color:var(--text-muted);">No results for "' + escA(q) + '"</div>';
    } else {
      results.innerHTML = matches.slice(0,8).map((m,i) => `
        <div onclick="globalSearchSelect(${i})" style="
          padding:10px 16px; cursor:pointer; display:flex; align-items:center; gap:12px;
          border-bottom:1px solid var(--border); font-size:13px;
          transition:background 0.15s;" onmouseover="this.style.background='var(--green-bg)'" onmouseout="this.style.background=''">
          <span style="font-size:18px;">${m.icon}</span>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escA(m.title)}</div>
            <div style="font-size:12px;color:var(--text-muted);">${escA(m.sub)}</div>
          </div>
          <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;background:var(--green-pale);color:var(--green-dark);text-transform:capitalize;white-space:nowrap;">${escA(m.badge||'')}</span>
        </div>`).join('');
      // Store matches for click handler
      window._searchMatches = matches;
    }
    results.style.display = 'block';
  }, 220);
}

function globalSearchSelect(idx) {
  const m = (window._searchMatches || [])[idx];
  if (m && m.action) m.action();
  const results = document.getElementById('globalSearchResults');
  if (results) results.style.display = 'none';
  const inp = document.getElementById('globalSearchInput');
  if (inp) inp.value = '';
}

// Close search results on outside click
document.addEventListener('click', e => {
  const results = document.getElementById('globalSearchResults');
  const input   = document.getElementById('globalSearchInput');
  if (results && input && !results.contains(e.target) && e.target !== input) {
    results.style.display = 'none';
  }
});

/* ════════════════════════════════════════════════════════
   NOTIFICATION PREFERENCES  — dynamic, saved to zoo_settings
════════════════════════════════════════════════════════ */
const _notifPrefKeys = {
  notifPrefTickets: 'notif_pref_tickets',
  notifPrefReviews: 'notif_pref_reviews',
  notifPrefEvents:  'notif_pref_events',
  notifPrefStars:   'notif_pref_stars',
};

async function loadNotifPrefs() {
  try {
    const res  = await fetch('api/staff.php?action=get_settings', { credentials: 'include' });
    const data = await res.json();
    if (!data.success || !data.settings) return;
    const s = data.settings;
    // Default all to ON (1) if not yet set
    document.getElementById('notifPrefTickets').checked = (s['notif_pref_tickets'] ?? '1') !== '0';
    document.getElementById('notifPrefReviews').checked = (s['notif_pref_reviews'] ?? '1') !== '0';
    document.getElementById('notifPrefEvents').checked  = (s['notif_pref_events']  ?? '1') !== '0';
    document.getElementById('notifPrefStars').checked   = (s['notif_pref_stars']   ?? '1') !== '0';
  } catch(e) {}
}

async function saveNotifPref(key, value) {
  const statusEl = document.getElementById('notifPrefStatus');
  try {
    const res  = await fetch('api/staff.php?action=save_setting', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key, value: value ? '1' : '0' }),
    });
    const data = await res.json();
    if (data.success) {
      if (statusEl) { statusEl.textContent = '✓ Preference saved'; setTimeout(() => { statusEl.textContent = ''; }, 2500); }
    } else {
      if (statusEl) statusEl.textContent = '⚠ Could not save — check api/staff.php';
    }
  } catch(e) {
    if (statusEl) statusEl.textContent = '⚠ Network error saving preference';
  }
}

// Load notification prefs on page load if settings page is active
document.addEventListener('DOMContentLoaded', () => {
  const active = document.querySelector('.page.active');
  if (active && active.id === 'page-settings') loadNotifPrefs();
});

/* ════════════════════════════════════════════════════════
   ZOO MAP EDITOR
════════════════════════════════════════════════════════ */
/* ════════════════════════════════════════════════════════
   ZOO MAP EDITOR (without animal dropdown)
════════════════════════════════════════════════════════ */
let _meMapData   = '';
let _mePins      = [];
let _meEditId    = null;
let _meZones     = [];
let _meDragId    = null;
let _meIsDragging= false;
let _meInited    = false;

async function initMapEditor() {
    if (_meInited) return;
    _meInited = true;

    try {
        const [mapRes, zoneRes] = await Promise.all([
            fetch('api/MapData.php', { credentials: 'include' }).then(r => r.json()),
            fetch('api/MapData.php?zones', { credentials: 'include' }).then(r => r.json()),
        ]);

        if (mapRes.error) console.error('Map data error:', mapRes.error);
        if (zoneRes.error) console.error('Zones error:', zoneRes.error);
        
        _meMapData = mapRes.Map || '';
        _mePins = (Array.isArray(mapRes.Pins) ? mapRes.Pins : Object.values(mapRes.Pins || []))
            .map(p => ({ ...p, pos: p.pos ?? { x: parseFloat(p.pos_x ?? 0), y: parseFloat(p.pos_y ?? 0) } }));
        _meZones = zoneRes.zones || [];

        const img = document.getElementById('mapEditorImg');
        if (img && _meMapData) img.src = _meMapData;

        _meRenderPins();
        _meRenderChips();
        _meBindDrag();
    } catch(e) {
        console.error('Failed to load map data:', e);
        showToast('Failed to load map data. Check database connection.', 'error');
    }
}

function _meMakePinSVG(color, emoji, active) {
  const shadow = active
    ? `drop-shadow(0 0 8px ${color})`
    : 'drop-shadow(0 2px 4px rgba(0,0,0,0.3))';
  return `<svg width="32" height="43" viewBox="0 0 40 54" style="filter:${shadow};display:block;">
    <path d="M20 2C11.16 2 4 9.16 4 18c0 12 16 34 16 34s16-22 16-34C36 9.16 28.84 2 20 2z"
      fill="${color}" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
    <circle cx="20" cy="18" r="10" fill="white" opacity="0.92"/>
    <text x="20" y="23" text-anchor="middle" font-size="11">${emoji}</text>
  </svg>`;
}

function _meRenderPins() {
  const container = document.getElementById('mapEditorContainer');
  if (!container) return;
  container.querySelectorAll('.me-pin-btn').forEach(el => el.remove());

  _mePins.forEach(pin => {
    const btn = document.createElement('button');
    btn.className  = 'me-pin-btn';
    btn.id         = 'mepin-' + pin.id;
    btn.title      = pin.name;
    btn.style.cssText = `position:absolute;background:none;border:none;cursor:grab;padding:0;
      transform:translate(-50%,-100%);left:${pin.pos.x}%;top:${pin.pos.y}%;
      z-index:${_meEditId === pin.id ? 10 : 1};`;
    btn.innerHTML  = _meMakePinSVG(pin.color, pin.emoji, _meEditId === pin.id);

    btn.addEventListener('click', () => { if (!_meIsDragging) _meSetEditId(_meEditId === pin.id ? null : pin.id); });
    btn.addEventListener('mousedown', e => _meStartDrag(e, pin.id));
    container.appendChild(btn);
  });
}

function _meRenderChips() {
  const wrap = document.getElementById('mapEditorChips');
  if (!wrap) return;
  wrap.innerHTML = '';
  _mePins.forEach(pin => {
    const chip = document.createElement('button');
    const isActive = _meEditId === pin.id;
    chip.style.cssText = `display:inline-flex;align-items:center;gap:6px;padding:5px 14px 5px 6px;
      border-radius:20px;cursor:pointer;font-size:13px;font-weight:${isActive?700:500};
      border:1.5px solid ${isActive ? pin.color : 'var(--border)'};
      background:${isActive ? (pin.light || pin.color + '22') : 'var(--white)'};
      color:${isActive ? pin.color : 'var(--text-mid)'};
      font-family:inherit;transition:all 0.15s;`;
    chip.innerHTML = _meMakePinSVG(pin.color, pin.emoji, false) + ' ' + pin.name;
    chip.addEventListener('click', () => _meSetEditId(_meEditId === pin.id ? null : pin.id));
    wrap.appendChild(chip);
  });
}

function _meSetEditId(id) {
  _meEditId = id;
  _meRenderPins();
  _meRenderChips();

  const drawer = document.getElementById('mapEditorDrawer');
  if (!drawer) return;

  if (!id) { drawer.style.display = 'none'; return; }

  const pin = _mePins.find(p => p.id === id);
  if (!pin) return;

  drawer.style.display = 'flex';
  const titleEl = document.getElementById('mapDrawerTitle');
  if (titleEl) { titleEl.textContent = pin.emoji + ' Edit Pin'; titleEl.style.color = pin.color; }

  document.getElementById('meInputName').value  = pin.name;
  document.getElementById('meInputEmoji').value = pin.emoji;
  document.getElementById('meInputColor').value = pin.color;
  document.getElementById('meInputDesc').value  = pin.desc ?? '';
  document.getElementById('mePosX').textContent = pin.pos.x.toFixed(1);
  document.getElementById('mePosY').textContent = pin.pos.y.toFixed(1);

  // Populate zone select
  const sel = document.getElementById('meInputZone');
  sel.innerHTML = '<option value="">— Select zone —</option>';
  if (_meZones && _meZones.length) {
      _meZones.forEach(z => {
          const opt = document.createElement('option');
          opt.value = z.location_name;
          opt.textContent = z.location_name;
          opt.selected = pin.zone === z.location_name;
          sel.appendChild(opt);
      });
  } else {
      // Fallback default zones if none from DB
      const defaultZones = ['Zone A', 'Zone B', 'Zone C'];
      defaultZones.forEach(zone => {
          const opt = document.createElement('option');
          opt.value = zone;
          opt.textContent = zone;
          opt.selected = pin.zone === zone;
          sel.appendChild(opt);
      });
  }

  // Remove any previous change handler and add new one (no animal loading)
  sel.onchange = () => {
      mapEditorSetField('zone', sel.value);
  };
}

function mapEditorCloseDrawer() {
  _meSetEditId(null);
}

function mapEditorSetField(key, value) {
  if (!_meEditId) return;
  _mePins = _mePins.map(p => p.id !== _meEditId ? p : { ...p, [key]: value });
  _meRenderPins();
  _meRenderChips();
  const pin = _mePins.find(p => p.id === _meEditId);
  if (pin) {
    const titleEl = document.getElementById('mapDrawerTitle');
    if (titleEl) { titleEl.textContent = pin.emoji + ' Edit Pin'; titleEl.style.color = pin.color; }
  }
}

function mapEditorDeletePin() {
  _mePins = _mePins.filter(p => p.id !== _meEditId);
  _meEditId = null;
  const drawer = document.getElementById('mapEditorDrawer');
  if (drawer) drawer.style.display = 'none';
  _meRenderPins();
  _meRenderChips();
  showToast('Pin deleted.');
}

function mapEditorAddPin() {
  const id = 'pin-' + Date.now();
  _mePins.push({
    id, name: 'New Location', emoji: '📍', color: '#2D5A27', light: '#EAF1E8',
    zone: '', desc: 'Describe this location.', animals: [],
    pos: { x: 50, y: 50 },
  });
  _meRenderPins();
  _meRenderChips();
  _meSetEditId(id);
  showToast('New pin added. Drag it into position.');
}

async function mapEditorSave() {
  const btn = document.getElementById('mapEditorSaveBtn');
  if (btn) { btn.disabled = true; btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> Saving…'; }
  try {
    const res  = await fetch('api/MapData.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ Map: _meMapData, Pins: _mePins }),
    });
    const data = await res.json();
    if (data.success) {
      showToast('Map saved successfully ✓');
      if (btn) btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> Saved!';
      setTimeout(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg> Save to Database'; }
      }, 2500);
    } else { throw new Error(data.error || 'API returned failure'); }
  } catch(e) {
    showToast('Save failed: ' + e.message, 'error');
    if (btn) { btn.disabled = false; btn.innerHTML = 'Save to Database'; }
  }
}

function mapEditorChangeMap(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    _meMapData = ev.target.result;
    const img = document.getElementById('mapEditorImg');
    if (img) img.src = _meMapData;
    showToast('Map image updated. Remember to save!');
  };
  reader.readAsDataURL(file);
  e.target.value = '';
}

function _meToPercent(e) {
  const img = document.getElementById('mapEditorImg');
  if (!img) return null;
  const r = img.getBoundingClientRect();
  return {
    x: Math.max(0, Math.min(100, ((e.clientX - r.left) / r.width)  * 100)),
    y: Math.max(0, Math.min(100, ((e.clientY - r.top)  / r.height) * 100)),
  };
}

function _meStartDrag(e, pinId) {
    e.preventDefault(); 
    e.stopPropagation();
    _meDragId = pinId;
    _meIsDragging = false;
    e.target.setAttribute('draggable', 'false');
}

function _meBindDrag() {
    const container = document.getElementById('mapEditorContainer');
    if (!container) return;

    container.removeEventListener('mousemove', _meOnMouseMove);
    container.removeEventListener('mouseup', _meStopDrag);
    container.removeEventListener('mouseleave', _meStopDrag);
    
    function _meOnMouseMove(e) {
        if (!_meDragId) return;
        _meIsDragging = true;
        const pos = _meToPercent(e);
        if (!pos) return;
        _mePins = _mePins.map(p => p.id !== _meDragId ? p : { ...p, pos });
        const btn = document.getElementById('mepin-' + _meDragId);
        if (btn) { 
            btn.style.left = pos.x + '%'; 
            btn.style.top = pos.y + '%'; 
        }
        if (_meDragId === _meEditId) {
            const px = document.getElementById('mePosX');
            const py = document.getElementById('mePosY');
            if (px) px.textContent = pos.x.toFixed(1);
            if (py) py.textContent = pos.y.toFixed(1);
        }
    }
    
    function _meStopDrag() {
        _meDragId = null;
        setTimeout(() => { _meIsDragging = false; }, 10);
    }
    
    container.addEventListener('mousemove', _meOnMouseMove);
    container.addEventListener('mouseup', _meStopDrag);
    container.addEventListener('mouseleave', _meStopDrag);
}

// ─── TNG SETTINGS ─────────────────────────────────────────────
let _newQrFile = null;

async function loadTngSettings() {
    try {
        const res = await fetch('api/tickets.php?action=tng_settings', { credentials: 'include' });
        const data = await res.json();
        if (data.success) {
            const qrImg = document.getElementById('currentTngQr');
            if (data.tng_qr_url) qrImg.src = data.tng_qr_url;
            else qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=TNG-PLACEHOLDER';
            document.getElementById('currentReceiverName').innerText = data.receiver_name || 'WildTrack Safari Park';
            document.getElementById('newReceiverName').value = data.receiver_name || '';
        }
    } catch(e) {}
}

function clearNewQr() {
    _newQrFile = null;
    document.getElementById('qrFileInput').value = '';
    document.getElementById('qrPreviewNew').style.display = 'none';
}

document.getElementById('qrFileInput').addEventListener('change', function(e) {
    if (!e.target.files.length) return;
    const file = e.target.files[0];
    if (file.size > 2 * 1024 * 1024) {
        showToast('File too large. Max 2MB.', 'error');
        clearNewQr();
        return;
    }
    _newQrFile = file;
    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('newQrPreview').src = ev.target.result;
        document.getElementById('qrPreviewNew').style.display = 'block';
    };
    reader.readAsDataURL(file);
});

function openPasswordModalForTng() {
    // Simple password prompt – you can replace with a nicer modal if needed
    const pwd = prompt('Enter your admin password to update TNG QR settings:');
    if (!pwd) return;
    updateTngSettings(pwd);
}

async function updateTngSettings(password) {
    const receiver = document.getElementById('newReceiverName').value.trim();
    if (!receiver) {
        showToast('Receiver name is required.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('receiver_name', receiver);
    formData.append('password', password);
    if (_newQrFile) {
        formData.append('qr_image', _newQrFile);
    }

    const btn = document.querySelector('#tab-payment-settings .btn-primary');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const res = await fetch('api/update_tng_settings.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            showToast('TNG settings updated ✓');
            loadTngSettings();      // refresh current preview
            clearNewQr();           // clear uploaded file
            // also update the visitor-facing data if needed – the next fetch will pick new data
        } else {
            showToast(data.message || 'Update failed.', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

// Call loadTngSettings when the Payment Settings tab is opened
(function() {
    const _origSwitchTab = window.switchTab;
    window.switchTab = function(btn, tabId) {
        _origSwitchTab(btn, tabId);
        if (tabId === 'tab-payment-settings') {
            loadTngSettings();
        }
    };
})();

async function refreshTngHistory() {
    const container = document.getElementById('tngHistoryList');
    if (!container) return;
    container.innerHTML = '<div style="color:var(--text-muted); text-align:center; padding:20px;">Loading history…</div>';

    try {
        const res = await fetch('api/get_tng_history.php', { credentials: 'include' });
        const data = await res.json();
        if (!data.success || !data.history.length) {
            container.innerHTML = '<div style="color:var(--text-muted); text-align:center; padding:20px;">No QR changes recorded yet.</div>';
            return;
        }

        let html = '<div style="background:var(--green-bg); border-radius:12px; overflow-x:auto;">';
        html += '<table style="width:100%; border-collapse:collapse; min-width:600px;">';
        html += '<thead><tr style="border-bottom:1px solid var(--border);">' +
                '<th style="padding:10px 12px; text-align:left;">Admin</th>' +
                '<th style="padding:10px 12px; text-align:left;">QR Image Changed</th>' +
                '<th style="padding:10px 12px; text-align:left;">Old Receiver</th>' +
                '<th style="padding:10px 12px; text-align:left;">New Receiver</th>' +
                '<th style="padding:10px 12px; text-align:left;">Changed At</th>' +
                '</tr></thead><tbody>';

        data.history.forEach(row => {
            const qrChanged = (row.old_qr_path !== row.new_qr_path);
            const qrBadge = qrChanged 
                ? '<span style="background:var(--green-pale); color:var(--green-dark); padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">✓ Yes</span>'
                : '<span style="color:var(--text-muted);">—</span>';

            html += `<tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 12px;">${escapeHtml(row.admin_name)}</td>
                        <td style="padding:10px 12px;">${qrBadge}</td>
                        <td style="padding:10px 12px; color:var(--text-muted);">${escapeHtml(row.old_receiver_name)}</td>
                        <td style="padding:10px 12px; font-weight:600;">${escapeHtml(row.new_receiver_name)}</td>
                        <td style="padding:10px 12px;">${new Date(row.changed_at).toLocaleString()}</td>
                      </tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    } catch(e) {
        container.innerHTML = '<div style="color:var(--text-muted); text-align:center; padding:20px;">Failed to load history.</div>';
    }
}

// Also call refreshTngHistory when the Payment Settings tab is opened
(function() {
    const _origSwitchTab = window.switchTab;
    window.switchTab = function(btn, tabId) {
        _origSwitchTab(btn, tabId);
        if (tabId === 'tab-payment-settings') {
            loadTngSettings();
            refreshTngHistory();
        }
    };
})();

</script>
</body>
</html>
