<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/check_session.php';
requireWorkerLogin();

// ── Fetch worker notifications from DB (uses existing notifications table) ──
$workerNotifs = [];
try {
    $pdo    = getDB();
    $userId = $_SESSION['user']['user_id'] ?? 0;

    // Map existing notification types to worker-friendly icons
    $iconMap = [
        'shift_start'        => ['🌅','ni-green'],
        'feeding_reminder'   => ['🍖','ni-orange'],
        'health_alert'       => ['🩺','ni-red'],
        'vaccination_due'    => ['💉','ni-orange'],
        'incident_flagged'   => ['🚨','ni-red'],
        'task_assigned'      => ['📋','ni-green'],
        'worker_general'     => ['🔔','ni-green'],
        // fallback for visitor/booking types still in table
        'booking_approved'   => ['✅','ni-green'],
        'booking_rejected'   => ['❌','ni-red'],
        'new_payment_proof'  => ['💳','ni-orange'],
        'new_feedback'       => ['💬','ni-green'],
        'feedback_reply'     => ['↩️','ni-green'],
    ];

    $stmt = $pdo->prepare(
        "SELECT id, type, title, body, is_read, created_at
         FROM notifications
         WHERE user_id = ?
         ORDER BY created_at DESC
         LIMIT 50"
    );
    $stmt->execute([$userId]);
    foreach ($stmt->fetchAll() as $r) {
        [$icon, $iconClass] = $iconMap[$r['type']] ?? ['🔔','ni-green'];
        $workerNotifs[] = [
            'id'        => (int)$r['id'],
            'icon'      => $icon,
            'iconClass' => $iconClass,
            'title'     => htmlspecialchars($r['title']),
            'sub'       => htmlspecialchars($r['body'] ?? ''),
            'unread'    => !(bool)$r['is_read'],
            'ts'        => strtotime($r['created_at']) * 1000,
        ];
    }
} catch (Exception $e) {
    $workerNotifs = null; // JS will fall back to localStorage
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WildTrack Worker Dashboard</title>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
:root{
  --sidebar:#2D5A27;--sidebar-hover:rgba(255,255,255,0.10);--sidebar-active:rgba(255,255,255,0.18);
  --header:#4e7a51;--teal:#76d7c4;--teal-dark:#5ab8a5;--bg:#f1f8e9;--text:#3a3a3a;
  --white:#ffffff;--border:#d6e8d6;--sub:#6b8f71;--orange:#e07b39;--red:#d94f3d;
  --shadow:0 2px 12px rgba(0,0,0,0.07);--shadow-lg:0 8px 32px rgba(0,0,0,0.13);
}
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:var(--bg);min-height:100vh;font-size:14px;color:var(--text);display:flex;}
.page{display:none;}.page.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
.page.active{animation:fadeUp 0.25s ease both;}

/* SIDEBAR */
.sidebar{width:200px;min-height:100vh;background:var(--sidebar);display:flex;flex-direction:column;position:fixed;top:0;left:0;z-index:100;box-shadow:2px 0 12px rgba(0,0,0,0.15);}
.sidebar-logo{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,0.12);}
.sidebar-logo-top{display:flex;align-items:center;gap:9px;}
.sidebar-logo-icon{width:36px;height:36px;background:rgba(255,255,255,0.18);border-radius:10px;display:flex;align-items:center;justify-content:center;}
.sidebar-logo-name{font-size:17px;font-weight:700;color:#fff;letter-spacing:0.3px;}
.sidebar-logo-sub{font-size:11px;color:rgba(255,255,255,0.6);margin-top:4px;text-transform:uppercase;letter-spacing:1px;}
.sidebar-section{padding:18px 0 6px;}
.sidebar-section-label{font-size:10px;font-weight:700;color:rgba(255,255,255,0.4);letter-spacing:1.2px;text-transform:uppercase;padding:0 18px;margin-bottom:6px;}
.nav-item{display:flex;align-items:center;gap:11px;padding:10px 18px;cursor:pointer;font-size:13px;font-weight:500;color:rgba(255,255,255,0.75);transition:all 0.18s;position:relative;}
.nav-item:hover{background:var(--sidebar-hover);color:#fff;}
.nav-item.active{background:var(--sidebar-active);color:#fff;font-weight:600;}
.nav-item.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--teal);border-radius:0 3px 3px 0;}
.nav-item-icon{font-size:16px;flex-shrink:0;width:18px;text-align:center;}
.nav-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;min-width:20px;text-align:center;}
.nav-badge.teal{background:var(--teal-dark);}
.sidebar-footer{margin-top:auto;padding:16px;border-top:1px solid rgba(255,255,255,0.12);}
.sidebar-user{display:flex;align-items:center;gap:10px;}
.sidebar-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.3);}
.sidebar-user-name{font-size:13px;font-weight:600;color:#fff;line-height:1.2;}
.sidebar-user-role{font-size:11px;color:rgba(255,255,255,0.55);}
.sidebar-logout-btn{margin-left:auto;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,0.12);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.7);transition:all 0.18s;}
.sidebar-logout-btn:hover{background:rgba(217,79,61,0.5);color:#fff;}

/* MAIN */
.main-content{margin-left:200px;flex:1;min-height:100vh;display:flex;flex-direction:column;}
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;box-shadow:0 1px 6px rgba(0,0,0,0.06);}
.topbar-left{display:flex;align-items:center;gap:14px;}
.topbar-page-title{font-size:18px;font-weight:700;color:var(--text);}
.live-badge{display:inline-flex;align-items:center;gap:5px;background:rgba(46,139,119,0.1);color:#2e8b77;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid rgba(46,139,119,0.25);}
.live-dot{width:6px;height:6px;background:#2e8b77;border-radius:50%;animation:pulse 1.5s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.4;}}
.topbar-right{display:flex;align-items:center;gap:12px;}

/* GLOBAL SEARCH */
.search-box{display:flex;align-items:center;gap:8px;background:var(--bg);border:1.5px solid var(--border);border-radius:10px;padding:8px 14px;width:220px;position:relative;}
.search-box input{border:none;outline:none;background:transparent;font-size:13px;color:var(--text);font-family:inherit;width:100%;}
.search-box input::placeholder{color:#bbb;}
.search-results-dropdown{position:absolute;top:calc(100% + 6px);left:0;right:0;background:var(--white);border:1.5px solid var(--border);border-radius:12px;box-shadow:var(--shadow-lg);z-index:999;max-height:320px;overflow-y:auto;display:none;}
.search-results-dropdown.open{display:block;}
.search-result-item{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--border);transition:background 0.15s;}
.search-result-item:last-child{border-bottom:none;}
.search-result-item:hover{background:rgba(78,122,81,0.06);}
.sri-icon{width:34px;height:34px;border-radius:9px;background:rgba(78,122,81,0.09);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.sri-title{font-size:13px;font-weight:600;color:var(--text);}
.sri-sub{font-size:11px;color:var(--sub);}
.search-no-results{padding:18px;text-align:center;color:var(--sub);font-size:13px;}
.search-category{font-size:10px;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:1px;padding:8px 14px 4px;background:rgba(78,122,81,0.03);}

/* NOTIFICATION PANEL */
.notif-wrapper{position:relative;}
.topbar-icon-btn{width:36px;height:36px;border-radius:9px;background:var(--bg);border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--sub);position:relative;}
.notif-dot{position:absolute;top:5px;right:5px;width:8px;height:8px;background:var(--red);border-radius:50%;border:2px solid var(--white);}
.notif-panel{position:absolute;top:calc(100% + 10px);right:0;width:340px;background:var(--white);border:1.5px solid var(--border);border-radius:14px;box-shadow:var(--shadow-lg);z-index:999;display:none;overflow:hidden;}
.notif-panel.open{display:block;animation:fadeUp 0.2s ease both;}
.notif-panel-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border);}
.notif-panel-title{font-size:14px;font-weight:700;color:var(--text);}
.notif-mark-all{font-size:11px;color:var(--header);font-weight:600;cursor:pointer;background:none;border:none;font-family:inherit;}
.notif-mark-all:hover{text-decoration:underline;}
.notif-list{max-height:300px;overflow-y:auto;}
.notif-item{display:flex;align-items:flex-start;gap:11px;padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;transition:background 0.15s;position:relative;}
.notif-item:last-child{border-bottom:none;}
.notif-item:hover{background:rgba(78,122,81,0.04);}
.notif-item.unread{background:rgba(118,215,196,0.07);}

/* Slide-in animation for new notification items */
@keyframes notifSlideIn{from{opacity:0;transform:translateX(18px);}to{opacity:1;transform:translateX(0);}}
.notif-item.new-item{animation:notifSlideIn 0.28s cubic-bezier(.22,.68,0,1.1) both;}

.notif-item-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.ni-green{background:rgba(118,215,196,0.18);}
.ni-orange{background:rgba(224,123,57,0.12);}
.ni-red{background:rgba(217,79,61,0.1);}
.notif-item-title{font-size:13px;font-weight:600;color:var(--text);line-height:1.3;}
.notif-item-sub{font-size:11px;color:var(--sub);margin-top:2px;}
.notif-unread-dot{position:absolute;top:14px;right:14px;width:7px;height:7px;border-radius:50%;background:var(--teal-dark);}
.notif-empty{padding:28px;text-align:center;color:var(--sub);font-size:13px;}
.notif-panel-footer{padding:10px 16px;border-top:1px solid var(--border);text-align:center;}
.notif-panel-footer a{font-size:12px;color:var(--header);font-weight:600;cursor:pointer;text-decoration:none;}
.notif-count-badge{background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:6px;}

.page-wrap{padding:28px 32px 80px;flex:1;}
.section-header{margin-bottom:22px;}
.section-header h2{font-size:22px;font-weight:700;color:var(--text);}
.section-header p{font-size:13px;color:var(--sub);margin-top:3px;}

/* HERO */
.hero-card{background:var(--sidebar);border-radius:16px;padding:26px 32px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 6px 24px rgba(45,90,39,0.2);margin-bottom:24px;position:relative;overflow:hidden;}
.hero-card::after{content:'';position:absolute;right:-20px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,0.05);}
.hero-card h1{font-size:24px;font-weight:700;color:#fff;line-height:1.3;margin-bottom:4px;}
.hero-card p{font-size:13px;color:rgba(255,255,255,0.65);}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px;}
.stat-card{background:var(--white);border-radius:14px;padding:20px 22px;box-shadow:var(--shadow);border:1.5px solid var(--border);}
.stat-card-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;}
.stat-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;}
.si-green{background:rgba(78,122,81,0.1);color:var(--header);}
.si-teal{background:rgba(118,215,196,0.2);color:var(--teal-dark);}
.si-orange{background:rgba(224,123,57,0.12);color:var(--orange);}
.si-red{background:rgba(217,79,61,0.1);color:var(--red);}
.stat-change{font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;}
.sc-up{background:rgba(46,139,119,0.1);color:#2e8b77;}
.sc-warn{background:rgba(224,123,57,0.12);color:var(--orange);}
.sc-alert{background:rgba(217,79,61,0.1);color:var(--red);}
.stat-val{font-size:30px;font-weight:700;color:var(--text);line-height:1;margin-bottom:3px;}
.stat-lbl{font-size:12px;color:var(--sub);}
.stat-underline{height:3px;border-radius:3px;margin-top:14px;}
.su-green{background:linear-gradient(90deg,var(--header),var(--teal));}
.su-orange{background:var(--orange);}
.su-red{background:var(--red);}
.su-teal{background:var(--teal);}

/* CARDS */
.cards-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
.dash-card{background:var(--white);border-radius:14px;padding:24px;box-shadow:var(--shadow);border:1.5px solid var(--border);cursor:pointer;transition:all 0.2s;}
.dash-card:hover{border-color:var(--teal);transform:translateY(-3px);box-shadow:var(--shadow-lg);}
.dc-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;}
.dc-icon{width:46px;height:46px;border-radius:12px;background:rgba(78,122,81,0.09);display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--header);}
.dc-badge{font-size:11px;font-weight:700;color:var(--header);background:rgba(175,213,177,0.3);padding:4px 10px;border-radius:8px;}
.dc-title{font-size:15px;font-weight:700;color:var(--text);margin-bottom:5px;}
.dc-desc{font-size:12px;color:var(--sub);line-height:1.6;}

/* BLOCK */
.block{background:var(--white);border-radius:14px;padding:22px 26px;box-shadow:var(--shadow);margin-bottom:18px;border:1px solid var(--border);}
.block-title{font-size:10px;font-weight:700;color:var(--sub);letter-spacing:1px;text-transform:uppercase;margin-bottom:16px;}

/* FORMS */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group.full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:600;color:var(--sub);}
.form-input,.form-select,.form-textarea{border:1.5px solid var(--border);border-radius:9px;padding:10px 13px;font-size:13px;font-family:inherit;color:var(--text);background:var(--white);outline:none;transition:border-color 0.2s;width:100%;}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--header);box-shadow:0 0 0 3px rgba(78,122,81,0.08);}
.form-textarea{resize:vertical;min-height:78px;}

/* BUTTONS */
.btn-primary{background:var(--header);color:#fff;border:none;padding:10px 22px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:7px;font-family:inherit;}
.btn-primary:hover{background:#3d6140;transform:translateY(-1px);box-shadow:0 4px 14px rgba(78,122,81,0.28);}
.btn-outline{background:var(--white);color:var(--text);border:1.5px solid var(--border);padding:10px 22px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:7px;font-family:inherit;}
.btn-outline:hover{border-color:var(--header);color:var(--header);}
.btn-danger{background:var(--white);color:var(--red);border:1.5px solid rgba(217,79,61,0.3);padding:10px 22px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:7px;font-family:inherit;}
.btn-danger:hover{background:rgba(217,79,61,0.06);border-color:var(--red);}

/* SEARCH BAR (page-level) */
.search-bar{display:flex;align-items:center;gap:10px;background:var(--white);border:1.5px solid var(--border);border-radius:10px;padding:10px 16px;}
.search-bar:focus-within{border-color:var(--header);}
.search-bar input{border:none;outline:none;flex:1;font-size:14px;background:transparent;color:var(--text);font-family:inherit;}
.search-bar input::placeholder{color:#bbb;}
.top-bar{display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;}

/* ANIMAL GRID */
.animal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.animal-card{background:var(--white);border-radius:14px;padding:20px;box-shadow:0 2px 8px rgba(78,122,81,0.07);border:1.5px solid var(--border);transition:all 0.2s;}
.animal-card:hover{border-color:var(--teal);box-shadow:var(--shadow);}
.animal-header{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.animal-emoji-wrap{width:48px;height:48px;border-radius:13px;background:rgba(78,122,81,0.08);display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;}
.animal-name{font-size:15px;font-weight:700;color:var(--text);}
.animal-species{font-size:11px;color:var(--sub);margin-top:2px;}
.animal-info{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:12px;}
.ai-item{font-size:12px;color:var(--sub);}
.ai-item span{font-weight:600;color:var(--text);}
.animal-footer{display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border);padding-top:11px;}
.status-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 11px;border-radius:20px;}
.sp-healthy{background:rgba(118,215,196,0.18);color:#2e8b77;}
.sp-watch{background:rgba(224,123,57,0.12);color:var(--orange);}
.sp-treatment{background:rgba(217,79,61,0.1);color:var(--red);}
.status-dot{width:6px;height:6px;border-radius:50%;}
.dot-green{background:#2e8b77;}.dot-orange{background:var(--orange);}.dot-red{background:var(--red);}
.card-actions{display:flex;gap:6px;}
.icon-btn{width:30px;height:30px;border-radius:8px;border:1.5px solid var(--border);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--sub);transition:all 0.18s;}
.icon-btn:hover{border-color:var(--header);color:var(--header);}
.icon-btn.del:hover{border-color:var(--red);color:var(--red);}
.empty-state{text-align:center;padding:50px 20px;color:var(--sub);grid-column:1/-1;}
.empty-icon{font-size:48px;margin-bottom:10px;opacity:0.35;}
.empty-state p{font-size:14px;}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.42);z-index:500;align-items:center;justify-content:center;padding:20px;}
.modal-overlay.open{display:flex;}
@keyframes mUp{from{transform:translateY(20px);opacity:0;}to{transform:translateY(0);opacity:1;}}
.modal{background:var(--white);border-radius:18px;padding:28px;width:100%;max-width:560px;box-shadow:var(--shadow-lg);animation:mUp 0.24s cubic-bezier(.22,.68,0,1.2) both;max-height:90vh;overflow-y:auto;}
.modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
.modal-title{font-size:18px;font-weight:700;color:var(--text);}
.modal-close{width:32px;height:32px;border-radius:8px;border:1.5px solid var(--border);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--sub);font-size:16px;font-family:inherit;}
.modal-close:hover{border-color:var(--red);color:var(--red);}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:22px;}
.emoji-picker{display:flex;flex-wrap:wrap;gap:7px;margin-top:6px;}
.emoji-opt{width:38px;height:38px;border-radius:9px;border:1.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;transition:all 0.15s;background:var(--white);}
.emoji-opt:hover,.emoji-opt.sel{border-color:var(--header);background:rgba(78,122,81,0.08);}

/* TABLE */
.table-wrap{overflow-x:auto;border-radius:11px;border:1.5px solid var(--border);}
.data-table{width:100%;border-collapse:collapse;font-size:13px;}
.data-table th{font-size:11px;font-weight:700;color:var(--sub);text-align:left;padding:11px 15px;border-bottom:1.5px solid var(--border);text-transform:uppercase;letter-spacing:0.5px;background:rgba(78,122,81,0.025);}
.data-table td{padding:12px 15px;color:var(--text);border-bottom:1px solid var(--border);}
.data-table tr:last-child td{border-bottom:none;}
.data-table tbody tr:hover td{background:rgba(78,122,81,0.022);}
.table-empty-row td{text-align:center;padding:36px 15px;color:var(--sub);font-size:13px;}

/* BADGES */
.badge{display:inline-block;font-size:11px;font-weight:700;padding:3px 10px;border-radius:7px;}
.b-green{background:rgba(118,215,196,0.18);color:#2e8b77;}
.b-orange{background:rgba(224,123,57,0.12);color:var(--orange);}
.b-red{background:rgba(217,79,61,0.1);color:var(--red);}

/* HEALTH */
.health-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;}
.hs-card{border-radius:12px;padding:18px 20px;text-align:center;border:1.5px solid var(--border);}
.hs-val{font-size:32px;font-weight:700;line-height:1;}
.hs-lbl{font-size:12px;color:var(--sub);margin-top:5px;}
.record-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);}
.record-row:last-child{border-bottom:none;}
.record-left{display:flex;align-items:center;gap:12px;}
.rec-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.ri-g{background:rgba(118,215,196,0.16);color:#2e8b77;}
.ri-o{background:rgba(224,123,57,0.12);color:var(--orange);}
.ri-r{background:rgba(217,79,61,0.1);color:var(--red);}
.rec-name{font-size:13px;font-weight:600;color:var(--text);}
.rec-sub{font-size:11px;color:var(--sub);margin-top:2px;}
.rec-date{font-size:11px;color:var(--sub);white-space:nowrap;}
.placeholder-msg{padding:32px 0;color:var(--sub);font-size:13px;text-align:center;}

/* VAX */
.vax-item{display:flex;align-items:center;justify-content:space-between;padding:13px 0;border-bottom:1px solid var(--border);}
.vax-item:last-child{border-bottom:none;}
.vax-left{display:flex;align-items:center;gap:12px;}
.vax-icon{width:38px;height:38px;border-radius:10px;background:rgba(78,122,81,0.08);display:flex;align-items:center;justify-content:center;font-size:17px;color:var(--header);}
.vax-name{font-size:13px;font-weight:600;color:var(--text);}
.vax-detail{font-size:11px;color:var(--sub);margin-top:2px;}
.vax-status{font-size:11px;font-weight:700;padding:3px 10px;border-radius:8px;}
.vs-done{background:rgba(118,215,196,0.18);color:#2e8b77;}

/* TASKS */
.progress-label{display:flex;justify-content:space-between;font-size:13px;color:var(--sub);margin-bottom:8px;font-weight:500;}
.progress-bar{height:8px;background:var(--bg);border-radius:8px;overflow:hidden;border:1px solid var(--border);}
.progress-fill{height:100%;background:linear-gradient(90deg,var(--header),var(--teal));border-radius:8px;transition:width 0.5s ease;}
.filter-row{display:flex;gap:7px;margin-bottom:18px;flex-wrap:wrap;}
.filter-btn{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid var(--border);cursor:pointer;transition:all 0.18s;background:var(--white);color:var(--sub);font-family:inherit;}
.filter-btn.active,.filter-btn:hover{border-color:var(--header);color:var(--header);background:rgba(78,122,81,0.06);}

/* Task card layout */
.task-item{display:flex;align-items:center;gap:12px;padding:13px 0;border-bottom:1px solid var(--border);transition:opacity 0.2s;}
.task-item:last-child{border-bottom:none;}
.task-item.inactive{opacity:0.45;}
.task-check{width:22px;height:22px;border-radius:6px;border:2px solid var(--border);cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all 0.2s;}
.task-check.done{background:var(--header);border-color:var(--header);}
.task-check.done::after{content:'✓';color:#fff;font-size:12px;font-weight:700;}
.task-check.inactive-check{border-color:#ccc;background:#f5f5f5;cursor:default;}
.task-text{flex:1;min-width:0;}
.task-name{font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.task-name.done{text-decoration:line-through;color:var(--sub);}
.task-name.inactive-name{color:var(--sub);}
.task-meta{font-size:11px;color:var(--sub);margin-top:2px;}
.task-pri{font-size:11px;font-weight:700;padding:3px 9px;border-radius:7px;flex-shrink:0;}
.tp-h{background:rgba(217,79,61,0.1);color:var(--red);}
.tp-m{background:rgba(224,123,57,0.12);color:var(--orange);}
.tp-l{background:rgba(118,215,196,0.18);color:#2e8b77;}
.task-actions{display:flex;gap:5px;flex-shrink:0;}
.task-toggle-btn{width:28px;height:28px;border-radius:7px;border:1.5px solid var(--border);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--sub);transition:all 0.18s;font-size:13px;}
.task-toggle-btn:hover{border-color:var(--teal-dark);color:var(--teal-dark);}
.task-toggle-btn.active-task{background:rgba(118,215,196,0.15);border-color:var(--teal-dark);color:var(--teal-dark);}
.task-edit-btn{width:28px;height:28px;border-radius:7px;border:1.5px solid var(--border);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--sub);transition:all 0.18s;}
.task-edit-btn:hover{border-color:var(--header);color:var(--header);}
.task-del-btn{width:28px;height:28px;border-radius:7px;border:1.5px solid var(--border);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--sub);transition:all 0.18s;}
.task-del-btn:hover{border-color:var(--red);color:var(--red);}

/* Real-time notification toast (floats top-right) */
.rt-notif{position:fixed;top:72px;right:22px;background:var(--white);border:1.5px solid var(--border);border-radius:13px;padding:12px 16px;display:flex;align-items:flex-start;gap:11px;box-shadow:var(--shadow-lg);z-index:9999;max-width:310px;transform:translateX(340px);transition:transform 0.35s cubic-bezier(.22,.68,0,1.2);pointer-events:none;}
.rt-notif.show{transform:translateX(0);}
.rt-notif-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.rt-notif-title{font-size:13px;font-weight:700;color:var(--text);}
.rt-notif-sub{font-size:11px;color:var(--sub);margin-top:2px;line-height:1.4;}
.rt-notif-bar{position:absolute;bottom:0;left:0;height:3px;background:var(--teal);border-radius:0 0 13px 13px;width:100%;transform-origin:left;transition:width linear;}

/* INCIDENTS */
.sev-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.sev-opt{border:2px solid var(--border);border-radius:12px;padding:14px 10px;text-align:center;cursor:pointer;transition:all 0.2s;background:var(--white);}
.sev-opt:hover{transform:translateY(-2px);}
.sev-opt.sel{border-color:var(--header);background:rgba(78,122,81,0.06);box-shadow:0 3px 10px rgba(78,122,81,0.12);}
.sev-emoji{font-size:22px;margin-bottom:5px;}
.sev-lbl{font-size:12px;font-weight:700;color:var(--text);}
.inc-timeline{display:flex;flex-direction:column;}
.inc-row{display:flex;gap:13px;padding:13px 0;border-bottom:1px solid var(--border);}
.inc-row:last-child{border-bottom:none;}
.inc-dot-col{display:flex;flex-direction:column;align-items:center;padding-top:3px;}
.inc-dot{width:11px;height:11px;border-radius:50%;flex-shrink:0;}
.inc-line{width:2px;flex:1;background:var(--border);margin-top:4px;min-height:16px;}
.inc-title{font-size:13px;font-weight:700;color:var(--text);}
.inc-desc{font-size:12px;color:var(--sub);margin-top:3px;line-height:1.55;}
.inc-meta{font-size:11px;color:var(--sub);margin-top:4px;}

/* TOAST */
.toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(16px);background:var(--text);color:#fff;padding:11px 22px;border-radius:11px;font-size:13px;font-weight:600;opacity:0;transition:all 0.3s;z-index:999;white-space:nowrap;box-shadow:0 5px 18px rgba(0,0,0,0.18);}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.toast.success{background:var(--header);}
.toast.error{background:var(--red);}

@media(max-width:1100px){.stats-row{grid-template-columns:repeat(2,1fr);}.cards-grid,.animal-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){
  .sidebar{width:60px;}.sidebar-logo-name,.sidebar-logo-sub,.sidebar-section-label,.nav-item span:not(.nav-item-icon),.nav-badge,.sidebar-user-name,.sidebar-user-role{display:none;}
  .main-content{margin-left:60px;}.page-wrap{padding:20px 16px 60px;}.topbar{padding:0 16px;}
  .stats-row{grid-template-columns:1fr 1fr;}
}
@media(max-width:520px){.stats-row,.cards-grid,.animal-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="toast" id="toast"></div>
<div class="rt-notif" id="rtNotif">
  <div class="rt-notif-icon ni-green" id="rtNotifIcon">🔔</div>
  <div>
    <div class="rt-notif-title" id="rtNotifTitle"></div>
    <div class="rt-notif-sub" id="rtNotifSub"></div>
  </div>
  <div class="rt-notif-bar" id="rtNotifBar"></div>
</div>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-top">
      <div class="sidebar-logo-icon"><span class="iconify" data-icon="lucide:shield-check" style="font-size:20px;color:#fff;"></span></div>
      <div class="sidebar-logo-name">WildTrack</div>
    </div>
    <div class="sidebar-logo-sub">Worker Portal</div>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Main</div>
    <div class="nav-item active" onclick="navTo('pg-dashboard',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:layout-dashboard" data-width="16"></span></span>
      <span>Overview</span>
    </div>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Animal Care</div>
    <div class="nav-item" onclick="navTo('pg-animals',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:paw-print" data-width="16"></span></span>
      <span>Animal Profiles</span>
    </div>
    <div class="nav-item" onclick="navTo('pg-feeding',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:cookie" data-width="16"></span></span>
      <span>Feeding</span>
      <span class="nav-badge teal" id="feedBadge">0</span>
    </div>
    <div class="nav-item" onclick="navTo('pg-health',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:heart-pulse" data-width="16"></span></span>
      <span>Health</span>
    </div>
    <div class="nav-item" onclick="navTo('pg-vaccination',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:syringe" data-width="16"></span></span>
      <span>Vaccination</span>
      <span class="nav-badge" id="vaxBadge">0</span>
    </div>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Shift</div>
    <div class="nav-item" onclick="navTo('pg-tasks',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:list-checks" data-width="16"></span></span>
      <span>Daily Tasks</span>
      <span class="nav-badge" id="sideTaskBadge">0</span>
    </div>
    <div class="nav-item" onclick="navTo('pg-incidents',this)">
      <span class="nav-item-icon"><span class="iconify" data-icon="lucide:alert-triangle" data-width="16"></span></span>
      <span>Incidents</span>
    </div>
  </div>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <img class="sidebar-avatar" src="https://i.pravatar.cc/64?img=3" alt="" id="sidebarAvatar">
      <div>
        <div class="sidebar-user-name" id="sidebarName">Worker</div>
        <div class="sidebar-user-role" id="sidebarRole">Caretaker</div>
      </div>
      <button class="sidebar-logout-btn" onclick="logout()" title="Logout">
        <span class="iconify" data-icon="lucide:log-out" data-width="14"></span>
      </button>
    </div>
  </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="topbar">
    <div class="topbar-left">
      <div class="topbar-page-title" id="topbarTitle">Overview</div>
      <div class="live-badge"><div class="live-dot"></div> LIVE</div>
    </div>
    <div class="topbar-right">

      <!-- GLOBAL SEARCH -->
      <div class="search-box" id="globalSearchBox">
        <span class="iconify" data-icon="lucide:search" style="font-size:14px;color:#bbb;flex-shrink:0;"></span>
        <input type="text" placeholder="Search anything..." id="globalSearchInput" autocomplete="off"
               oninput="handleGlobalSearch(this.value)" onfocus="handleGlobalSearch(this.value)" />
        <div class="search-results-dropdown" id="searchDropdown"></div>
      </div>

      <!-- NOTIFICATION BELL -->
      <div class="notif-wrapper">
        <div class="topbar-icon-btn" id="notifBtn" onclick="toggleNotifPanel()" title="Notifications">
          <span class="iconify" data-icon="lucide:bell" style="font-size:16px;"></span>
          <div class="notif-dot" id="notifDot" style="display:none;"></div>
        </div>
        <div class="notif-panel" id="notifPanel">
          <div class="notif-panel-header">
            <span class="notif-panel-title">Notifications <span class="notif-count-badge" id="notifCountBadge">0</span></span>
            <button class="notif-mark-all" onclick="markAllRead()">Mark all read</button>
          </div>
          <div class="notif-list" id="notifList"></div>
          <div class="notif-panel-footer"><a onclick="clearAllNotifs()">Clear all notifications</a></div>
        </div>
      </div>

    </div>
  </div>

  <!-- DASHBOARD -->
  <div class="page active" id="pg-dashboard">
  <div class="page-wrap">
    <div class="hero-card">
      <div>
        <h1 id="greetingText">Good Morning, Worker 👋</h1>
        <p id="liveDate">Loading...</p>
      </div>
      <span class="iconify" data-icon="lucide:leaf" style="font-size:72px;color:rgba(255,255,255,0.18);position:relative;z-index:1;"></span>
    </div>
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon si-green"><span class="iconify" data-icon="lucide:paw-print" data-width="20"></span></div><div class="stat-change sc-up">Live</div></div>
        <div class="stat-val" id="dashAnimalCount">0</div>
        <div class="stat-lbl">Animals Assigned</div>
        <div class="stat-underline su-green"></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon si-teal"><span class="iconify" data-icon="lucide:list-checks" data-width="20"></span></div><div class="stat-change sc-up" id="dashTaskChange">0 done</div></div>
        <div class="stat-val" id="dashTaskCount">0</div>
        <div class="stat-lbl">Tasks Today</div>
        <div class="stat-underline su-teal"></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon si-orange"><span class="iconify" data-icon="lucide:syringe" data-width="20"></span></div><div class="stat-change sc-warn">Logged</div></div>
        <div class="stat-val" id="dashVaxCount">0</div>
        <div class="stat-lbl">Vaccinations Recorded</div>
        <div class="stat-underline su-orange"></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon si-red"><span class="iconify" data-icon="lucide:alert-triangle" data-width="20"></span></div><div class="stat-change sc-alert" id="dashIncChange">0 Active</div></div>
        <div class="stat-val" id="dashIncCount">0</div>
        <div class="stat-lbl">Incidents Reported</div>
        <div class="stat-underline su-red"></div>
      </div>
    </div>
    <div class="cards-grid">
      <div class="dash-card" onclick="navTo('pg-animals',document.querySelectorAll('.nav-item')[1])">
        <div class="dc-top"><div class="dc-icon"><span class="iconify" data-icon="lucide:paw-print"></span></div><div class="dc-badge" id="dashAnimalBadge">0 Animals</div></div>
        <div class="dc-title">Animal Profiles</div>
        <div class="dc-desc">Add, edit and manage all animal records including diet, health status and enclosure details.</div>
      </div>
      <div class="dash-card" onclick="navTo('pg-feeding',document.querySelectorAll('.nav-item')[2])">
        <div class="dc-top"><div class="dc-icon"><span class="iconify" data-icon="lucide:cookie"></span></div><div class="dc-badge" id="dashFeedBadge">0 Logged Today</div></div>
        <div class="dc-title">Feeding Management</div>
        <div class="dc-desc">Log feeding schedules, food types and track daily consumption per animal.</div>
      </div>
      <div class="dash-card" onclick="navTo('pg-health',document.querySelectorAll('.nav-item')[3])">
        <div class="dc-top"><div class="dc-icon"><span class="iconify" data-icon="lucide:heart-pulse"></span></div><div class="dc-badge" id="dashHealthBadge">0 Records</div></div>
        <div class="dc-title">Animal Health</div>
        <div class="dc-desc">Monitor illness signs, vet visits, medications and full medical treatment records.</div>
      </div>
      <div class="dash-card" onclick="navTo('pg-vaccination',document.querySelectorAll('.nav-item')[4])">
        <div class="dc-top"><div class="dc-icon"><span class="iconify" data-icon="lucide:syringe"></span></div><div class="dc-badge" id="dashVaxBadge">0 Records</div></div>
        <div class="dc-title">Vaccination Tracker</div>
        <div class="dc-desc">Manage vaccination history and get reminders for upcoming due schedules.</div>
      </div>
      <div class="dash-card" onclick="navTo('pg-tasks',document.querySelectorAll('.nav-item')[5])">
        <div class="dc-top"><div class="dc-icon"><span class="iconify" data-icon="lucide:list-checks"></span></div><div class="dc-badge" id="dashTaskBadge">0 Remaining</div></div>
        <div class="dc-title">Daily Tasks</div>
        <div class="dc-desc">View, complete and add feeding, cleaning and health check assignments for your shift.</div>
      </div>
      <div class="dash-card" onclick="navTo('pg-incidents',document.querySelectorAll('.nav-item')[6])">
        <div class="dc-top"><div class="dc-icon"><span class="iconify" data-icon="lucide:alert-triangle"></span></div><div class="dc-badge" id="dashIncBadge">0 This Week</div></div>
        <div class="dc-title">Incident Reports</div>
        <div class="dc-desc">Submit reports for injuries, escape attempts or any unusual animal behaviour events.</div>
      </div>
    </div>
  </div>
  </div>

  <!-- ANIMAL PROFILES -->
  <div class="page" id="pg-animals">
  <div class="page-wrap">
    <div class="section-header"><h2>Animal Profiles</h2><p>View, add, edit and delete animal records.</p></div>
    <div class="top-bar">
      <div class="search-bar" style="flex:1;max-width:380px;">
        <span class="iconify" data-icon="lucide:search" style="color:#bbb;font-size:16px;flex-shrink:0;"></span>
        <input type="text" placeholder="Search by name or species..." id="animalSearch" oninput="renderAnimals()">
      </div>
      <select class="form-select" id="statusFilter" onchange="renderAnimals()" style="width:180px;">
        <option value="all">All Statuses</option><option value="healthy">Healthy</option>
        <option value="watch">Under Observation</option><option value="treatment">Under Treatment</option>
      </select>
      <button class="btn-primary" onclick="openAnimalModal()"><span class="iconify" data-icon="lucide:plus" data-width="15"></span> Add Animal</button>
    </div>
    <div class="animal-grid" id="animalGrid">
      <div class="empty-state"><div class="empty-icon">🐾</div><p>No animals yet. Click "Add Animal" to get started.</p></div>
    </div>
  </div>
  </div>

  <!-- FEEDING -->
  <div class="page" id="pg-feeding">
  <div class="page-wrap">
    <div class="section-header"><h2>Feeding Management</h2><p>Log and track all animal feeding sessions for your shift.</p></div>
    <div class="block">
      <div class="block-title">Log New Feeding</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Animal</label><select class="form-select" id="feedAnimal"></select></div>
        <div class="form-group"><label class="form-label">Food Type</label>
          <select class="form-select" id="feedType"><option>Raw Meat</option><option>Fruits &amp; Vegetables</option><option>Pellets</option><option>Fish</option><option>Grass / Hay</option><option>Mixed Diet</option></select>
        </div>
        <div class="form-group"><label class="form-label">Quantity (kg)</label><input class="form-input" type="number" id="feedQty" placeholder="e.g. 5.0" min="0" step="0.1"></div>
        <div class="form-group"><label class="form-label">Amount Consumed</label>
          <select class="form-select" id="feedConsumed"><option>All Eaten</option><option>75% Eaten</option><option>50% Eaten</option><option>25% Eaten</option><option>Refused Food</option></select>
        </div>
        <div class="form-group full"><label class="form-label">Notes (optional)</label><textarea class="form-textarea" id="feedNotes" placeholder="Appetite, behaviour observations..."></textarea></div>
      </div>
      <div style="margin-top:16px;"><button class="btn-primary" onclick="logFeeding()"><span class="iconify" data-icon="lucide:plus-circle" data-width="15"></span> Log Feeding</button></div>
    </div>
    <div class="block">
      <div class="block-title">Today's Feeding Log</div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Time</th><th>Animal</th><th>Food Type</th><th>Qty (kg)</th><th>Consumed</th><th>Notes</th><th>Logged By</th><th>Actions</th></tr></thead>
          <tbody id="feedingTableBody">
            <tr class="table-empty-row"><td colspan="8">No feedings logged yet. Log a feeding above to see records here.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="block">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div class="block-title" style="margin-bottom:0;">Feeding History</div>
        <div style="display:flex;align-items:center;gap:8px;">
          <label style="font-size:12px;color:var(--sub);font-weight:600;">Filter by date:</label>
          <input type="date" id="historyDateFilter" class="form-input" style="padding:5px 10px;font-size:12px;width:150px;" onchange="loadFeedingHistory()">
          <button class="btn-secondary" style="padding:5px 14px;font-size:12px;" onclick="clearHistoryFilter()">All</button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Date</th><th>Time</th><th>Animal</th><th>Food Type</th><th>Qty (kg)</th><th>Consumed</th><th>Notes</th><th>Logged By</th></tr></thead>
          <tbody id="feedingHistoryBody">
            <tr class="table-empty-row"><td colspan="8">Loading history...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div>

  <!-- HEALTH -->
  <div class="page" id="pg-health">
  <div class="page-wrap">
    <div class="section-header"><h2>Animal Health</h2><p>Monitor health records, vet visits and medical treatments.</p></div>
    <div class="health-stats">
      <div class="hs-card" style="background:rgba(118,215,196,0.1);border-color:rgba(118,215,196,0.35);"><div class="hs-val" style="color:#2e8b77;" id="hcHealthy">0</div><div class="hs-lbl">Healthy</div></div>
      <div class="hs-card" style="background:rgba(224,123,57,0.08);border-color:rgba(224,123,57,0.25);"><div class="hs-val" style="color:var(--orange);" id="hcWatch">0</div><div class="hs-lbl">Under Observation</div></div>
      <div class="hs-card" style="background:rgba(217,79,61,0.07);border-color:rgba(217,79,61,0.2);"><div class="hs-val" style="color:var(--red);" id="hcTreatment">0</div><div class="hs-lbl">Under Treatment</div></div>
    </div>
    <div class="block">
      <div class="block-title">Log Health Event</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Animal</label><select class="form-select" id="healthAnimal"></select></div>
        <div class="form-group"><label class="form-label">Event Type</label>
          <select class="form-select" id="healthType"><option>Routine Check</option><option>Illness Observation</option><option>Vet Visit</option><option>Medication Given</option><option>Post-Surgery Observation</option><option>Injury Report</option></select>
        </div>
        <div class="form-group full"><label class="form-label">Observations / Diagnosis</label><textarea class="form-textarea" id="healthNotes" placeholder="Symptoms, behaviour observations..."></textarea></div>
        <div class="form-group"><label class="form-label">Treatment (if any)</label><input class="form-input" type="text" id="healthTreatment" placeholder="e.g. Antibiotics 5ml, Wound dressing..."></div>
        <div class="form-group"><label class="form-label">Next Checkup Date</label><input class="form-input" type="date" id="healthNextCheckup"></div>
      </div>
      <div style="margin-top:16px;"><button class="btn-primary" onclick="logHealth()"><span class="iconify" data-icon="lucide:stethoscope" data-width="15"></span> Save Record</button></div>
    </div>
    <div class="block">
      <div class="block-title">Recent Health Records</div>
      <div id="healthRecords">
        <p class="placeholder-msg">No health records yet. Save a record above to see it here.</p>
      </div>
    </div>
  </div>
  </div>

  <!-- VACCINATION -->
  <div class="page" id="pg-vaccination">
  <div class="page-wrap">
    <div class="section-header"><h2>Vaccination Tracker</h2><p>Track vaccination history and manage upcoming schedules.</p></div>
    <div class="block">
      <div class="block-title">Record Vaccination</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Animal</label><select class="form-select" id="vaxAnimal"></select></div>
        <div class="form-group"><label class="form-label">Vaccine Name</label>
          <select class="form-select" id="vaxName"><option>Rabies Vaccine</option><option>Distemper Vaccine</option><option>Hepatitis Vaccine</option><option>Tetanus Booster</option><option>FMD Vaccine</option><option>Other</option></select>
        </div>
        <div class="form-group"><label class="form-label">Date Given</label><input class="form-input" type="date" id="vaxDate"></div>
        <div class="form-group"><label class="form-label">Next Due Date</label><input class="form-input" type="date" id="vaxNext"></div>
        <div class="form-group full"><label class="form-label">Administered By (Vet Name)</label><input class="form-input" type="text" id="vaxVet" placeholder="e.g. Dr. Lim"></div>
      </div>
      <div style="margin-top:16px;"><button class="btn-primary" onclick="logVaccination()"><span class="iconify" data-icon="lucide:syringe" data-width="15"></span> Save Record</button></div>
    </div>
    <div class="block">
      <div class="block-title">Vaccination Schedule</div>
      <div id="vaxList">
        <p class="placeholder-msg">No vaccination records yet. Save a record above to see it here.</p>
      </div>
    </div>
  </div>
  </div>

  <!-- TASKS -->
  <div class="page" id="pg-tasks">
  <div class="page-wrap">
    <div class="section-header"><h2>Daily Tasks</h2><p>Manage shift tasks — add, edit, activate/deactivate, complete, and delete.</p></div>
    <div class="block">
      <div class="progress-label"><span id="taskDoneLabel">0 of 0 completed</span><span id="taskPctLabel" style="font-weight:700;color:var(--header);">0%</span></div>
      <div class="progress-bar"><div class="progress-fill" id="taskBar" style="width:0%;"></div></div>
    </div>
    <div class="block">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div class="filter-row" style="margin-bottom:0;">
          <button class="filter-btn active" onclick="filterTasks('all',this)">All</button>
          <button class="filter-btn" onclick="filterTasks('active',this)">Active</button>
          <button class="filter-btn" onclick="filterTasks('pending',this)">Pending</button>
          <button class="filter-btn" onclick="filterTasks('done',this)">Completed</button>
          <button class="filter-btn" onclick="filterTasks('inactive',this)">Inactive</button>
        </div>
        <button class="btn-primary" style="padding:8px 16px;font-size:13px;" onclick="openTaskModal()">
          <span class="iconify" data-icon="lucide:plus" data-width="14"></span> Add Task
        </button>
      </div>
      <div id="taskList"></div>
    </div>
  </div>
  </div>

  <!-- INCIDENTS -->
  <div class="page" id="pg-incidents">
  <div class="page-wrap">
    <div class="section-header"><h2>Incident Reports</h2><p>Submit and review reports for injuries, escapes or unusual animal behaviour.</p></div>
    <div class="block">
      <div class="block-title">Submit New Report</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Animal Involved</label><select class="form-select" id="incAnimal"><option value="">Select animal (if any)...</option><option>N/A — Worker Only</option></select></div>
        <div class="form-group"><label class="form-label">Incident Type</label>
          <select class="form-select" id="incType"><option>Aggressive Behavior</option><option>Injury to Animal</option><option>Injury to Worker</option><option>Escape Attempt</option><option>Abnormal Behavior</option><option>Equipment Failure</option><option>Other</option></select>
        </div>
        <div class="form-group full"><label class="form-label">Severity Level</label>
          <div class="sev-grid">
            <div class="sev-opt sel" onclick="pickSev(this,'low')"><div class="sev-emoji">🟢</div><div class="sev-lbl">Low</div></div>
            <div class="sev-opt" onclick="pickSev(this,'medium')"><div class="sev-emoji">🟡</div><div class="sev-lbl">Medium</div></div>
            <div class="sev-opt" onclick="pickSev(this,'high')"><div class="sev-emoji">🔴</div><div class="sev-lbl">High</div></div>
          </div>
        </div>
        <div class="form-group full"><label class="form-label">Description</label><textarea class="form-textarea" style="min-height:90px;" id="incDesc" placeholder="What happened — when, where, and any actions taken..."></textarea></div>
      </div>
      <div style="margin-top:16px;"><button class="btn-primary" onclick="submitIncident()"><span class="iconify" data-icon="lucide:send" data-width="15"></span> Submit Report</button></div>
    </div>
    <div class="block">
      <div class="block-title">Incident History</div>
      <div class="inc-timeline" id="incidentHistory">
        <p class="placeholder-msg">No incidents reported yet. Submit a report above if needed.</p>
      </div>
    </div>
  </div>
  </div>
</div>

<!-- ADD/EDIT ANIMAL MODAL -->
<div class="modal-overlay" id="animalModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modalTitle">Add New Animal</div>
      <button class="modal-close" onclick="closeAnimalModal()">✕</button>
    </div>
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Name *</label><input class="form-input" type="text" id="mName" placeholder="e.g. Simba"></div>
      <div class="form-group"><label class="form-label">Species *</label><input class="form-input" type="text" id="mSpecies" placeholder="e.g. African Lion"></div>
      <div class="form-group"><label class="form-label">Age</label><input class="form-input" type="text" id="mAge" placeholder="e.g. 6 yrs"></div>
      <div class="form-group"><label class="form-label">Weight</label><input class="form-input" type="text" id="mWeight" placeholder="e.g. 190 kg"></div>
      <div class="form-group"><label class="form-label">Diet</label>
        <select class="form-select" id="mDiet"><option>Raw Meat</option><option>Fruits &amp; Vegetables</option><option>Fish</option><option>Grass / Hay</option><option>Mixed Diet</option><option>Omnivore</option></select>
      </div>
      <div class="form-group"><label class="form-label">Health Status</label>
        <select class="form-select" id="mStatus"><option value="healthy">Healthy</option><option value="watch">Under Observation</option><option value="treatment">Under Treatment</option></select>
      </div>
      <div class="form-group"><label class="form-label">Gender</label>
        <select class="form-select" id="mGender"><option>Male</option><option>Female</option></select>
      </div>
      <div class="form-group">
        <label class="form-label">Zone Area</label>
        <input class="form-input" type="text" id="mZone" placeholder="e.g. Zone A, Zone B, North Wing...">
      </div>
      <div class="form-group full"><label class="form-label">Choose Emoji Icon</label><div class="emoji-picker" id="emojiPicker"></div></div>
      <div class="form-group full"><label class="form-label">Notes</label><textarea class="form-textarea" id="mNotes" placeholder="Behavioural notes, special diet needs, medical background..."></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn-outline" onclick="closeAnimalModal()">Cancel</button>
      <button class="btn-primary" onclick="saveAnimal()"><span class="iconify" data-icon="lucide:save" data-width="14"></span><span id="modalSaveLbl">Add Animal</span></button>
    </div>
  </div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header">
      <div class="modal-title" style="color:var(--red);">Delete Animal Profile?</div>
      <button class="modal-close" onclick="closeDeleteModal()">✕</button>
    </div>
    <p style="font-size:14px;color:var(--sub);line-height:1.65;">You are about to permanently delete <strong id="deleteAnimalName" style="color:var(--text);"></strong>. This action cannot be undone.</p>
    <div class="modal-footer">
      <button class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn-danger" onclick="confirmDelete()"><span class="iconify" data-icon="lucide:trash-2" data-width="14"></span> Delete</button>
    </div>
  </div>
</div>

<!-- EDIT FEEDING MODAL -->
<div class="modal-overlay" id="editFeedModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header"><div class="modal-title">Edit Feeding Record</div><button class="modal-close" onclick="closeModal('editFeedModal')">✕</button></div>
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Food Type</label><select class="form-select" id="ef_type"><option>Raw Meat</option><option>Fruits &amp; Vegetables</option><option>Pellets</option><option>Fish</option><option>Grass / Hay</option><option>Mixed Diet</option></select></div>
      <div class="form-group"><label class="form-label">Quantity (kg)</label><input class="form-input" type="number" id="ef_qty" min="0" step="0.1"></div>
      <div class="form-group"><label class="form-label">Amount Consumed</label><select class="form-select" id="ef_consumed"><option>All Eaten</option><option>75% Eaten</option><option>50% Eaten</option><option>25% Eaten</option><option>Refused Food</option></select></div>
      <div class="form-group full"><label class="form-label">Notes</label><textarea class="form-textarea" id="ef_notes" placeholder="Notes..."></textarea></div>
    </div>
    <div class="modal-footer"><button class="btn-outline" onclick="closeModal('editFeedModal')">Cancel</button><button class="btn-primary" onclick="saveEditFeeding()"><span class="iconify" data-icon="lucide:save" data-width="14"></span> Save Changes</button></div>
  </div>
</div>

<!-- EDIT HEALTH MODAL -->
<div class="modal-overlay" id="editHealthModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header"><div class="modal-title">Edit Health Record</div><button class="modal-close" onclick="closeModal('editHealthModal')">✕</button></div>
    <div class="form-grid">
      <div class="form-group full"><label class="form-label">Event Type</label><select class="form-select" id="eh_type"><option>Routine Check</option><option>Illness Observation</option><option>Vet Visit</option><option>Medication Given</option><option>Post-Surgery Observation</option><option>Injury Report</option></select></div>
      <div class="form-group full"><label class="form-label">Observations / Diagnosis</label><textarea class="form-textarea" id="eh_notes" placeholder="Symptoms, behaviour observations..."></textarea></div>
      <div class="form-group"><label class="form-label">Treatment (if any)</label><input class="form-input" type="text" id="eh_treatment" placeholder="e.g. Antibiotics 5ml..."></div>
      <div class="form-group"><label class="form-label">Next Checkup Date</label><input class="form-input" type="date" id="eh_next_checkup"></div>
    </div>
    <div class="modal-footer"><button class="btn-outline" onclick="closeModal('editHealthModal')">Cancel</button><button class="btn-primary" onclick="saveEditHealth()"><span class="iconify" data-icon="lucide:save" data-width="14"></span> Save Changes</button></div>
  </div>
</div>

<!-- EDIT VAX MODAL -->
<div class="modal-overlay" id="editVaxModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header"><div class="modal-title">Edit Vaccination Record</div><button class="modal-close" onclick="closeModal('editVaxModal')">✕</button></div>
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Vaccine Name</label><select class="form-select" id="ev_name"><option>Rabies Vaccine</option><option>Distemper Vaccine</option><option>Hepatitis Vaccine</option><option>Tetanus Booster</option><option>FMD Vaccine</option><option>Other</option></select></div>
      <div class="form-group"><label class="form-label">Date Given</label><input class="form-input" type="date" id="ev_date"></div>
      <div class="form-group"><label class="form-label">Next Due Date</label><input class="form-input" type="date" id="ev_next"></div>
      <div class="form-group"><label class="form-label">Vet Name</label><input class="form-input" type="text" id="ev_vet" placeholder="e.g. Dr. Lim"></div>
    </div>
    <div class="modal-footer"><button class="btn-outline" onclick="closeModal('editVaxModal')">Cancel</button><button class="btn-primary" onclick="saveEditVax()"><span class="iconify" data-icon="lucide:save" data-width="14"></span> Save Changes</button></div>
  </div>
</div>

<!-- EDIT INCIDENT MODAL -->
<div class="modal-overlay" id="editIncModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header"><div class="modal-title">Edit Incident Report</div><button class="modal-close" onclick="closeModal('editIncModal')">✕</button></div>
    <div class="form-grid">
      <div class="form-group full"><label class="form-label">Description</label><textarea class="form-textarea" style="min-height:90px;" id="ei_desc" placeholder="What happened..."></textarea></div>
    </div>
    <div class="modal-footer"><button class="btn-outline" onclick="closeModal('editIncModal')">Cancel</button><button class="btn-primary" onclick="saveEditIncident()"><span class="iconify" data-icon="lucide:save" data-width="14"></span> Save Changes</button></div>
  </div>
</div>

<!-- ADD / EDIT TASK MODAL -->
<div class="modal-overlay" id="taskModal">
  <div class="modal" style="max-width:500px;">
    <div class="modal-header">
      <div class="modal-title" id="taskModalTitle">Add New Task</div>
      <button class="modal-close" onclick="closeModal('taskModal')">✕</button>
    </div>
    <div class="form-grid">
      <div class="form-group full">
        <label class="form-label">Task Description *</label>
        <input class="form-input" type="text" id="tm_name" placeholder="e.g. Clean enclosure 3, refill water tanks...">
      </div>
      <div class="form-group full">
        <label class="form-label">Details / Notes</label>
        <input class="form-input" type="text" id="tm_meta" placeholder="e.g. Enclosure 3 · Est. 30 min">
      </div>
      <div class="form-group">
        <label class="form-label">Priority</label>
        <select class="form-select" id="tm_priority">
          <option value="low">Low</option>
          <option value="med" selected>Medium</option>
          <option value="high">High</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Assigned Zone</label>
        <input class="form-input" type="text" id="tm_zone" placeholder="e.g. Zone A, North Wing, Reptile House...">
      </div>
      <div class="form-group full">
        <label class="form-label">Status</label>
        <select class="form-select" id="tm_active">
          <option value="true">Active (visible &amp; assignable)</option>
          <option value="false">Inactive (hidden from active list)</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-outline" onclick="closeModal('taskModal')">Cancel</button>
      <button class="btn-primary" onclick="saveTask()">
        <span class="iconify" data-icon="lucide:save" data-width="14"></span>
        <span id="taskModalSaveLbl">Add Task</span>
      </button>
    </div>
  </div>
</div>

<script>
// ─── PAGE & NAV ───
const PAGE_TITLES = {
  'pg-dashboard':'Overview','pg-animals':'Animal Profiles','pg-feeding':'Feeding Management',
  'pg-health':'Animal Health','pg-vaccination':'Vaccination Tracker','pg-tasks':'Daily Tasks','pg-incidents':'Incident Reports'
};
function openPage(id){
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  window.scrollTo(0,0);
  document.getElementById('topbarTitle').textContent=PAGE_TITLES[id]||'Overview';
  closeSearchDropdown();
  syncDashboard();
}
function navTo(pageId,navEl){
  openPage(pageId);
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  if(navEl)navEl.classList.add('active');
}

// ─── LOGOUT ───
async function logout(){
  if(!confirm('Are you sure you want to logout?'))return;
  try{await fetch('http://localhost/WildTrack/api/auth.php?action=logout',{method:'POST',credentials:'include'});}catch(e){}
  window.location.href='staff-login.php';
}

// ─── SESSION ───
let currentWorker=null;
async function initSession(){
  try{
    const res=await fetch('http://localhost/WildTrack/api/auth.php?action=me',{credentials:'include'});
    const data=await res.json();
    if(!data.success||(data.user.role!=='worker'&&data.user.role!=='admin')){window.location.href='staff-login.php';return;}
    currentWorker=data.user;
    document.getElementById('sidebarName').textContent=data.user.username;
    document.getElementById('sidebarRole').textContent=data.user.role==='admin'?'Administrator':'Caretaker';
    updateGreeting();
    await loadAnimals();           // loads animals → feeding log → health log
    await loadIncidentsFromAPI();
    await loadVaccinationsFromAPI();
    await loadTasksFromAPI();
  }catch(e){
    window.location.href='staff-login.php';
  }
}

// ─── REAL-TIME GREETING ───
function getGreeting(){
  const h=new Date().getHours();
  if(h>=5&&h<12)return'Good Morning';
  if(h>=12&&h<17)return'Good Afternoon';
  if(h>=17&&h<21)return'Good Evening';
  return'Good Night';
}
function updateGreeting(){
  const name=currentWorker?currentWorker.username:'Worker';
  document.getElementById('greetingText').textContent=getGreeting()+', '+name+' 👋';
  const now=new Date();
  document.getElementById('liveDate').textContent=now.toLocaleString('en-MY',{weekday:'long',year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'});
}
setInterval(()=>{
  const now=new Date();
  document.getElementById('liveDate').textContent=now.toLocaleString('en-MY',{weekday:'long',year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'});
  if(currentWorker){
    document.getElementById('greetingText').textContent=getGreeting()+', '+currentWorker.username+' 👋';
  }
},1000);

// ─── TOAST ───
function showToast(msg,type='success'){
  const t=document.getElementById('toast');
  t.textContent=msg;t.className='toast '+type+' show';
  setTimeout(()=>{t.className='toast';},3000);
}

// ─── LOCALSTORAGE HELPERS ───
const LS = {
  get(key,fallback=null){ try{ const v=localStorage.getItem('wt_'+key); return v?JSON.parse(v):fallback; }catch(e){ return fallback; } },
  set(key,val){ try{ localStorage.setItem('wt_'+key,JSON.stringify(val)); }catch(e){} }
};

// ═══════════════════════════════════════════════════════════
// ── NOTIFICATION SYSTEM ──
// • Loaded fresh from DB on every page load via PHP injection
// • pushNotif() saves new ones to DB via notifications_worker.php
// • localStorage used only as offline fallback
// ═══════════════════════════════════════════════════════════

const DB_NOTIFS = <?php echo $workerNotifs !== null ? json_encode($workerNotifs) : 'null'; ?>;

let notifications;
if (DB_NOTIFS !== null) {
  notifications = DB_NOTIFS;
  LS.set('notifications', notifications);
} else {
  // DB unavailable — use cached localStorage only, no fake defaults
  notifications = LS.get('notifications', []);
}
let notifNextId = notifications.length ? Math.max(...notifications.map(n => n.id)) + 1 : 1;

// BroadcastChannel for cross-tab real-time sync
let notifChannel = null;
try {
  notifChannel = new BroadcastChannel('wt_notifications');
  notifChannel.onmessage = e => {
    if (e.data && e.data.type === 'new_notif') {
      notifications = LS.get('notifications', notifications);
      notifNextId = notifications.length ? Math.max(...notifications.map(n => n.id)) + 1 : 1;
      // Live-update panel if open, else just badge
      if (document.getElementById('notifPanel').classList.contains('open')) {
        _insertNotifItem(e.data);
      }
      _updateNotifBadge();
      showRTNotif(e.data.icon, e.data.iconClass, e.data.title, e.data.sub);
    }
  };
} catch(e) {}

// Listen for storage changes from other tabs
window.addEventListener('storage', e => {
  if (e.key === 'wt_notifications') {
    notifications = e.newValue ? JSON.parse(e.newValue) : [];
    notifNextId = notifications.length ? Math.max(...notifications.map(n => n.id)) + 1 : 1;
    if (document.getElementById('notifPanel').classList.contains('open')) {
      renderNotifPanel();
    } else {
      _updateNotifBadge();
    }
  }
});

function timeAgo(ts) {
  const s = Math.floor((Date.now() - ts) / 1000);
  if (s < 60) return 'Just now';
  if (s < 3600) return Math.floor(s / 60) + ' min ago';
  if (s < 86400) return Math.floor(s / 3600) + ' hr' + (Math.floor(s/3600)>1?'s':'') + ' ago';
  if (s < 172800) return 'Yesterday';
  return Math.floor(s / 86400) + ' days ago';
}

// Update relative timestamps every 60s when panel is open
setInterval(() => {
  if (document.getElementById('notifPanel').classList.contains('open')) {
    // Just refresh the time labels without full re-render
    document.querySelectorAll('[data-notif-time]').forEach(el => {
      const ts = parseInt(el.getAttribute('data-notif-time'));
      if (ts) el.textContent = timeAgo(ts);
    });
  }
  _updateNotifBadge();
}, 60000);

// ── Badge & dot update (instant) ──
function _updateNotifBadge() {
  const unread = notifications.filter(n => n.unread).length;
  const badge = document.getElementById('notifCountBadge');
  const dot = document.getElementById('notifDot');
  if (badge) badge.textContent = unread;
  if (dot) dot.style.display = unread > 0 ? 'block' : 'none';
}

// ── Build a single notification item HTML ──
function _buildNotifItemHTML(n, isNew = false) {
  return `
    <div class="notif-item ${n.unread ? 'unread' : ''} ${isNew ? 'new-item' : ''}"
         id="notif-item-${n.id}"
         onclick="markOneRead(${n.id})">
      <div class="notif-item-icon ${n.iconClass}">${n.icon}</div>
      <div style="flex:1;min-width:0;">
        <div class="notif-item-title">${n.title}</div>
        <div class="notif-item-sub">${n.sub}</div>
        <div class="notif-item-sub" style="margin-top:3px;" data-notif-time="${n.ts||Date.now()}">${timeAgo(n.ts||Date.now())}</div>
      </div>
      ${n.unread ? '<div class="notif-unread-dot"></div>' : ''}
    </div>`;
}

// ── Full re-render of the notification panel list ──
function renderNotifPanel() {
  const list = document.getElementById('notifList');
  _updateNotifBadge();

  if (!notifications.length) {
    list.innerHTML = '<div class="notif-empty">🔔 No notifications yet.</div>';
    return;
  }
  list.innerHTML = notifications.map(n => _buildNotifItemHTML(n, false)).join('');
}

// ── Insert a single new item at the top of the open panel (live update) ──
function _insertNotifItem(notif) {
  const list = document.getElementById('notifList');
  if (!list) return;
  // Remove empty state if present
  const empty = list.querySelector('.notif-empty');
  if (empty) empty.remove();
  // Prepend new item with animation
  const div = document.createElement('div');
  div.innerHTML = _buildNotifItemHTML(notif, true);
  list.prepend(div.firstElementChild);
  // Cap displayed items at 50
  const items = list.querySelectorAll('.notif-item');
  if (items.length > 50) items[items.length - 1].remove();
}

function toggleNotifPanel() {
  const panel = document.getElementById('notifPanel');
  const isOpen = panel.classList.contains('open');
  closeAllDropdowns();
  if (!isOpen) {
    panel.classList.add('open');
    renderNotifPanel(); // full render on open
  }
}

function markOneRead(id) {
  const n = notifications.find(n => n.id === id);
  if (n && n.unread) {
    n.unread = false;
    LS.set('notifications', notifications);
    fetch('api/notifications_worker.php',{method:'PATCH',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'read',id})}).catch(()=>{});
    const el = document.getElementById('notif-item-' + id);
    if (el) { el.classList.remove('unread'); const dot=el.querySelector('.notif-unread-dot'); if(dot)dot.remove(); }
    _updateNotifBadge();
  }
}

function markAllRead() {
  notifications.forEach(n => n.unread = false);
  LS.set('notifications', notifications);
  fetch('api/notifications_worker.php',{method:'PATCH',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'read_all'})}).catch(()=>{});
  renderNotifPanel();
  showToast('✅ All notifications marked as read.');
}

function clearAllNotifs() {
  notifications = [];
  LS.set('notifications', notifications);
  fetch('api/notifications_worker.php',{method:'DELETE',credentials:'include'}).catch(()=>{});
  renderNotifPanel();
  showToast('🗑️ Notifications cleared.');
}

// ── Push a new notification — updates panel, badge & saves to DB ──
function pushNotif(icon, iconClass, title, sub) {
  const entry = { id: notifNextId++, icon, iconClass, title, sub, unread: true, ts: Date.now() };
  notifications.unshift(entry);
  if (notifications.length > 50) notifications = notifications.slice(0, 50);
  LS.set('notifications', notifications);

  // Persist to DB — map iconClass back to a worker type
  const typeMap = {'ni-green':'worker_general','ni-orange':'feeding_reminder','ni-red':'incident_flagged'};
  fetch('api/notifications_worker.php', {
    method: 'POST', credentials: 'include',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ type: typeMap[iconClass]||'worker_general', title, body: sub })
  }).catch(()=>{});

  try { notifChannel && notifChannel.postMessage({type:'new_notif',...entry}); } catch(e) {}
  const panel = document.getElementById('notifPanel');
  if (panel && panel.classList.contains('open')) _insertNotifItem(entry);
  _updateNotifBadge();
  showRTNotif(icon, iconClass, title, sub);
}

// ── REAL-TIME TOAST (slides in top-right) ──
let rtTimer = null;
function showRTNotif(icon, iconClass, title, sub) {
  const el = document.getElementById('rtNotif');
  const bar = document.getElementById('rtNotifBar');
  document.getElementById('rtNotifIcon').textContent = icon;
  document.getElementById('rtNotifIcon').className = 'rt-notif-icon ' + iconClass;
  document.getElementById('rtNotifTitle').textContent = title;
  document.getElementById('rtNotifSub').textContent = sub;
  if (rtTimer) clearTimeout(rtTimer);
  bar.style.transition = 'none';
  bar.style.width = '100%';
  el.classList.add('show');
  requestAnimationFrame(() => {
    bar.style.transition = 'width 4s linear';
    bar.style.width = '0%';
  });
  rtTimer = setTimeout(() => el.classList.remove('show'), 4200);
}

// ═══════════════════════════════════════════════════════════
// ── GLOBAL SEARCH SYSTEM ──
// ═══════════════════════════════════════════════════════════
const PAGES_INDEX = [
  {page:'pg-animals', navIdx:1, icon:'🐾', label:'Animal Profiles', sub:'Manage animal records'},
  {page:'pg-feeding', navIdx:2, icon:'🍖', label:'Feeding Management', sub:'Log animal feedings'},
  {page:'pg-health',  navIdx:3, icon:'🩺', label:'Animal Health', sub:'Health records & vet visits'},
  {page:'pg-vaccination', navIdx:4, icon:'💉', label:'Vaccination Tracker', sub:'Vaccination history'},
  {page:'pg-tasks',   navIdx:5, icon:'📋', label:'Daily Tasks', sub:'Shift task list'},
  {page:'pg-incidents',navIdx:6,icon:'⚠️', label:'Incident Reports', sub:'Report incidents'},
  {page:'pg-dashboard',navIdx:0,icon:'📊', label:'Dashboard Overview', sub:'Main overview'},
];

function handleGlobalSearch(q){
  q = q.trim().toLowerCase();
  const dd = document.getElementById('searchDropdown');
  if(!q){ dd.classList.remove('open'); dd.innerHTML=''; return; }

  const results = [];

  const pageMatches = PAGES_INDEX.filter(p=>
    p.label.toLowerCase().includes(q) || p.sub.toLowerCase().includes(q)
  );
  if(pageMatches.length){
    results.push({type:'category', label:'Pages'});
    pageMatches.forEach(p=>results.push({type:'page', ...p}));
  }

  const animalMatches = animals.filter(a=>
    a.name.toLowerCase().includes(q) ||
    a.species.toLowerCase().includes(q) ||
    (a.zone||'').toLowerCase().includes(q) ||
    a.status.toLowerCase().includes(q) ||
    (a.notes||'').toLowerCase().includes(q)
  );
  if(animalMatches.length){
    results.push({type:'category', label:'Animals'});
    animalMatches.slice(0,5).forEach(a=>results.push({type:'animal', ...a}));
  }

  const taskMatches = tasks.filter(t=>
    t.name.toLowerCase().includes(q) ||
    t.meta.toLowerCase().includes(q) ||
    t.zone.toLowerCase().includes(q)
  );
  if(taskMatches.length){
    results.push({type:'category', label:'Tasks'});
    taskMatches.slice(0,4).forEach(t=>results.push({type:'task', ...t}));
  }

  const feedMatches = feedingLog.filter(r=>
    r.animal.toLowerCase().includes(q) ||
    r.type.toLowerCase().includes(q) ||
    (r.notes||'').toLowerCase().includes(q)
  );
  if(feedMatches.length){
    results.push({type:'category', label:'Feeding Records'});
    feedMatches.slice(0,3).forEach(r=>results.push({type:'feed', ...r}));
  }

  const incMatches = incidentLog.filter(r=>
    r.title.toLowerCase().includes(q) ||
    r.desc.toLowerCase().includes(q)
  );
  if(incMatches.length){
    results.push({type:'category', label:'Incidents'});
    incMatches.slice(0,3).forEach(r=>results.push({type:'incident', ...r}));
  }

  if(!results.length){
    dd.innerHTML = `<div class="search-no-results">No results found for "<strong>${q}</strong>"</div>`;
    dd.classList.add('open');
    return;
  }

  dd.innerHTML = results.map(r=>{
    if(r.type==='category') return `<div class="search-category">${r.label}</div>`;
    if(r.type==='page') return `<div class="search-result-item" onclick="searchGoToPage('${r.page}',${r.navIdx})"><div class="sri-icon">${r.icon}</div><div><div class="sri-title">${r.label}</div><div class="sri-sub">${r.sub}</div></div></div>`;
    if(r.type==='animal'){
      const st={healthy:'Healthy',watch:'Under Observation',treatment:'Under Treatment'};
      return `<div class="search-result-item" onclick="searchGoToPage('pg-animals',1)"><div class="sri-icon">${r.emoji}</div><div><div class="sri-title">${r.name}</div><div class="sri-sub">${r.species} · ${st[r.status]}</div></div></div>`;
    }
    if(r.type==='task') return `<div class="search-result-item" onclick="searchGoToPage('pg-tasks',5)"><div class="sri-icon">📋</div><div><div class="sri-title">${r.name}</div><div class="sri-sub">${r.zone} · ${r.done?'Completed':'Pending'}</div></div></div>`;
    if(r.type==='feed') return `<div class="search-result-item" onclick="searchGoToPage('pg-feeding',2)"><div class="sri-icon">🍖</div><div><div class="sri-title">${r.animal} — ${r.type}</div><div class="sri-sub">${r.time} · ${r.consumed}</div></div></div>`;
    if(r.type==='incident') return `<div class="search-result-item" onclick="searchGoToPage('pg-incidents',6)"><div class="sri-icon">⚠️</div><div><div class="sri-title">${r.title}</div><div class="sri-sub">${r.meta}</div></div></div>`;
    return '';
  }).join('');
  dd.classList.add('open');
}

function searchGoToPage(pageId, navIdx){
  const navItems = document.querySelectorAll('.nav-item');
  navTo(pageId, navItems[navIdx]);
  closeSearchDropdown();
  document.getElementById('globalSearchInput').value='';
}

function closeSearchDropdown(){
  document.getElementById('searchDropdown').classList.remove('open');
}

function closeAllDropdowns(){
  closeSearchDropdown();
  document.getElementById('notifPanel').classList.remove('open');
}

document.addEventListener('click', e=>{
  const searchBox = document.getElementById('globalSearchBox');
  const notifWrapper = document.querySelector('.notif-wrapper');
  if(!searchBox.contains(e.target)) closeSearchDropdown();
  if(!notifWrapper.contains(e.target)) document.getElementById('notifPanel').classList.remove('open');
});

// ─── ANIMALS ───
const EMOJIS=['🦁','🐯','🐘','🦍','🐼','🐺','🦊','🐆','🐬','🦓','🦏','🐊','🦅','🦜','🦒','🐻','🦌','🐍','🦈','🐧','🦩'];
const API='api/animals_worker.php';
let animals=[],editingId=null,deletingId=null,selEmoji='🦁';

async function loadAnimals(){
  try{
    const res=await fetch(API,{credentials:'include'});
    const data=await res.json();
    if(data.success){ animals=data.animals; LS.set('animals',animals); }
    else throw new Error(data.message);
  }catch(e){ animals=LS.get('animals',[]); }
  renderAnimals();syncDashboard();populateDropdowns();
  // Load dependent data after animals are ready
  await loadFeedingLog();
  await loadHealthLog();
}

function buildEmojiPicker(){
  document.getElementById('emojiPicker').innerHTML=EMOJIS.map(e=>
    `<div class="emoji-opt${e===selEmoji?' sel':''}" onclick="pickEmoji(this,'${e}')">${e}</div>`).join('');
}
function pickEmoji(el,e){document.querySelectorAll('.emoji-opt').forEach(o=>o.classList.remove('sel'));el.classList.add('sel');selEmoji=e;}

function renderAnimals(){
  const q=(document.getElementById('animalSearch')||{value:''}).value.toLowerCase();
  const sf=(document.getElementById('statusFilter')||{value:'all'}).value;
  const list=animals.filter(a=>(a.name.toLowerCase().includes(q)||a.species.toLowerCase().includes(q))&&(sf==='all'||a.status===sf));
  const g=document.getElementById('animalGrid');
  if(!list.length){
    g.innerHTML=`<div class="empty-state"><div class="empty-icon">🐾</div><p>${animals.length?'No animals match your search.':'No animals yet. Click "Add Animal" to get started.'}</p></div>`;
    return;
  }
  const sp={healthy:'sp-healthy',watch:'sp-watch',treatment:'sp-treatment'};
  const dt={healthy:'dot-green',watch:'dot-orange',treatment:'dot-red'};
  const st={healthy:'Healthy',watch:'Under Observation',treatment:'Under Treatment'};
  g.innerHTML=list.map(a=>`
    <div class="animal-card">
      <div class="animal-header">
        <div class="animal-emoji-wrap">${a.emoji}</div>
        <div><div class="animal-name">${a.name}</div><div class="animal-species">${a.species}</div></div>
      </div>
      <div class="animal-info">
        <div class="ai-item">Age: <span>${a.age}</span></div>
        <div class="ai-item">Weight: <span>${a.weight}</span></div>
        <div class="ai-item">Diet: <span>${a.diet}</span></div>
        <div class="ai-item">Gender: <span>${a.gender}</span></div>
        ${a.zone?`<div class="ai-item">Zone: <span>${a.zone}</span></div>`:''}
        ${a.notes?`<div class="ai-item" style="grid-column:1/-1">Notes: <span>${a.notes}</span></div>`:''}
      </div>
      <div class="animal-footer">
        <span class="status-pill ${sp[a.status]}"><span class="status-dot ${dt[a.status]}"></span>${st[a.status]}</span>
        <div class="card-actions">
          <button class="icon-btn" title="Edit" onclick="openAnimalModal(${a.id})"><span class="iconify" data-icon="lucide:pencil" data-width="13"></span></button>
          <button class="icon-btn del" title="Delete" onclick="openDeleteModal(${a.id})"><span class="iconify" data-icon="lucide:trash-2" data-width="13"></span></button>
        </div>
      </div>
    </div>`).join('');
}

function openAnimalModal(id=null){
  editingId=id;
  if(id){
    const a=animals.find(a=>a.id===id);
    document.getElementById('modalTitle').textContent='Edit Animal';
    document.getElementById('modalSaveLbl').textContent='Save Changes';
    document.getElementById('mName').value=a.name;
    document.getElementById('mSpecies').value=a.species;
    document.getElementById('mAge').value=a.age;
    document.getElementById('mWeight').value=a.weight;
    document.getElementById('mDiet').value=a.diet;
    document.getElementById('mStatus').value=a.status;
    document.getElementById('mGender').value=a.gender;
    document.getElementById('mZone').value=a.zone||'';
    document.getElementById('mNotes').value=a.notes;
    selEmoji=a.emoji;
  }else{
    document.getElementById('modalTitle').textContent='Add New Animal';
    document.getElementById('modalSaveLbl').textContent='Add Animal';
    ['mName','mSpecies','mAge','mWeight','mNotes','mZone'].forEach(id=>document.getElementById(id).value='');
    ['mDiet','mStatus','mGender'].forEach(id=>document.getElementById(id).selectedIndex=0);
    selEmoji='🦁';
  }
  buildEmojiPicker();
  document.getElementById('animalModal').classList.add('open');
}
function closeAnimalModal(){document.getElementById('animalModal').classList.remove('open');}

async function saveAnimal(){
  const name=document.getElementById('mName').value.trim();
  const species=document.getElementById('mSpecies').value.trim();
  if(!name||!species){showToast('Name and Species are required.','error');return;}
  const d={name,species,age:document.getElementById('mAge').value.trim()||'Unknown',weight:document.getElementById('mWeight').value.trim()||'Unknown',diet:document.getElementById('mDiet').value,status:document.getElementById('mStatus').value,gender:document.getElementById('mGender').value,zone:document.getElementById('mZone').value.trim(),notes:document.getElementById('mNotes').value.trim(),emoji:selEmoji};
  const method=editingId?'PUT':'POST';
  if(editingId)d.id=editingId;
  try{
    const res=await fetch(API,{method,credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    const data=await res.json();
    if(data.success){showToast(editingId?`✏️ ${name} updated!`:`🐾 ${name} added!`);closeAnimalModal();await loadAnimals();}
    else showToast(data.message,'error');
  }catch(e){
    if(editingId){const idx=animals.findIndex(a=>a.id===editingId);if(idx>-1)animals[idx]={...animals[idx],...d};showToast(`✏️ ${name} updated!`);}
    else{
      const newId=animals.length?Math.max(...animals.map(a=>a.id))+1:1;
      animals.push({id:newId,...d});
      showToast(`🐾 ${name} added!`);
      pushNotif('🐾','ni-green',`New animal added`,`${name} (${species}) has been added to the system.`);
    }
    LS.set('animals',animals);
    closeAnimalModal();renderAnimals();syncDashboard();populateDropdowns();
  }
}

function openDeleteModal(id){
  deletingId=id;
  const a=animals.find(a=>a.id===id);
  document.getElementById('deleteAnimalName').textContent=`${a.name} (${a.species})`;
  document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('open');}
async function confirmDelete(){
  const a=animals.find(a=>a.id===deletingId);
  try{
    const res=await fetch(API,{method:'DELETE',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:deletingId})});
    const data=await res.json();
    if(data.success){closeDeleteModal();await loadAnimals();showToast(`🗑️ ${a.name} removed.`);}
    else showToast(data.message,'error');
  }catch(e){
    animals=animals.filter(x=>x.id!==deletingId);
    LS.set('animals',animals);
    closeDeleteModal();renderAnimals();syncDashboard();populateDropdowns();
    showToast(`🗑️ ${a.name} removed.`);
  }
}
document.querySelectorAll('.modal-overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open');}));

// ─── DROPDOWNS ───
function populateDropdowns(){
  const opts=animals.map(a=>`<option>${a.name} (${a.species})</option>`).join('');
  const base=`<option value="">Select animal...</option>`+opts;
  ['feedAnimal','healthAnimal','vaxAnimal'].forEach(id=>{const el=document.getElementById(id);if(el)el.innerHTML=base;});
  const inc=document.getElementById('incAnimal');
  if(inc)inc.innerHTML=`<option value="">Select animal (if any)...</option>`+opts+`<option>N/A — Worker Only</option>`;
}
populateDropdowns();

// ─── HEALTH COUNTS ───
function updateHealthCounts(){
  document.getElementById('hcHealthy').textContent=animals.filter(a=>a.status==='healthy').length;
  document.getElementById('hcWatch').textContent=animals.filter(a=>a.status==='watch').length;
  document.getElementById('hcTreatment').textContent=animals.filter(a=>a.status==='treatment').length;
}

// ─── COUNTERS ───
let feedingCount=0;
let healthCount=0;
let vaxCount=0;
let incidentCount=0;

function syncDashboard(){
  document.getElementById('dashAnimalCount').textContent=animals.length;
  document.getElementById('dashAnimalBadge').textContent=animals.length+' Animals';
  document.getElementById('dashFeedBadge').textContent=feedingCount+' Logged Today';
  document.getElementById('dashHealthBadge').textContent=healthCount+' Records';
  document.getElementById('dashVaxBadge').textContent=vaxCount+' Records';
  document.getElementById('dashVaxCount').textContent=vaxCount;
  document.getElementById('dashIncCount').textContent=incidentCount;
  document.getElementById('dashIncChange').textContent=incidentCount+' Active';
  document.getElementById('dashIncBadge').textContent=incidentCount+' This Week';
  document.getElementById('feedBadge').textContent=feedingCount;
  document.getElementById('vaxBadge').textContent=vaxCount;
  updateHealthCounts();
  updateTaskProgress();
}

// ─── FEEDING ───
let feedingLog=LS.get('feedingLog',[]);
async function loadFeedingLog(){
  try{
    const res=await fetch('api/feeding_worker.php',{credentials:'include'});
    const data=await res.json();
    if(data.success){ feedingLog=data.records; LS.set('feedingLog',feedingLog); }
  }catch(e){ feedingLog=LS.get('feedingLog',[]); }
  feedingCount=feedingLog.length; LS.set('feedingCount',feedingCount);
  renderFeedingTable(); syncDashboard();
}
function renderFeedingTable(){
  const tbody=document.getElementById('feedingTableBody');
  if(!feedingLog.length){tbody.innerHTML='<tr class="table-empty-row"><td colspan="8">No feedings logged yet. Log a feeding above to see records here.</td></tr>';return;}
  const bc={'All Eaten':'b-green','Refused Food':'b-red','75% Eaten':'b-orange','50% Eaten':'b-orange','25% Eaten':'b-red'};
  tbody.innerHTML=feedingLog.map((r,i)=>`<tr><td>${r.time}</td><td>${r.animal}</td><td>${r.type}</td><td>${r.qty}</td><td><span class="badge ${bc[r.consumed]||'b-orange'}">${r.consumed}</span></td><td style="color:var(--sub);font-size:12px;">${r.notes||'—'}</td><td>${r.worker}</td><td><div class="card-actions"><button class="icon-btn" title="Edit" onclick="editFeeding(${i})"><span class="iconify" data-icon="lucide:pencil" data-width="13"></span></button><button class="icon-btn del" title="Delete" onclick="deleteFeeding(${i})"><span class="iconify" data-icon="lucide:trash-2" data-width="13"></span></button></div></td></tr>`).join('');
}
async function logFeeding(){
  const animalVal=document.getElementById('feedAnimal').value;
  const qty=document.getElementById('feedQty').value;
  if(!animalVal||!qty){showToast('Please select an animal and enter quantity.','error');return;}
  const type=document.getElementById('feedType').value;
  const consumed=document.getElementById('feedConsumed').value;
  const notes=document.getElementById('feedNotes').value.trim();
  const time=new Date().toLocaleTimeString('en-MY',{hour:'2-digit',minute:'2-digit',hour12:false});
  const timeMySQL=new Date().toTimeString().slice(0,8);
  const workerName=currentWorker?currentWorker.username:'Worker';
  // Find animal_id from name
  const animal=animals.find(a=>animalVal.startsWith(a.name));
  const entry={time,animal:animalVal.split(' ')[0],type,qty:parseFloat(qty).toFixed(1),consumed,notes,worker:workerName};
  try{
    const res=await fetch('api/feeding_worker.php',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({animal_id:animal?animal.id:null,animal_name:entry.animal,food_type:type,quantity:qty,consumed,notes,feeding_time:timeMySQL})});
    const data=await res.json();
    if(data.success){ await loadFeedingLog(); }
    else { feedingLog.unshift(entry); LS.set('feedingLog',feedingLog); renderFeedingTable(); }
  }catch(e){ feedingLog.unshift(entry); LS.set('feedingLog',feedingLog); feedingCount=feedingLog.length; LS.set('feedingCount',feedingCount); renderFeedingTable(); }
  pushNotif('🍖','ni-green','Feeding logged',`${entry.animal} fed ${type} (${qty}kg) — ${consumed}`);
  document.getElementById('feedQty').value='';document.getElementById('feedNotes').value='';
  syncDashboard();showToast('🍖 Feeding logged successfully!');
}

// ─── HEALTH ───
let healthLog=LS.get('healthLog',[]);
async function loadHealthLog(){
  try{
    const res=await fetch('api/health_worker.php',{credentials:'include'});
    const data=await res.json();
    if(data.success){
      healthLog=data.records.map(r=>({...r, rawNextCheckup: r.rawNextCheckup||''}));
      LS.set('healthLog',healthLog);
    }
  }catch(e){ healthLog=LS.get('healthLog',[]); }
  healthCount=healthLog.length; LS.set('healthCount',healthCount);
  renderHealthRecords(); syncDashboard();
}
function renderHealthRecords(){
  const container=document.getElementById('healthRecords');
  if(!healthLog.length){container.innerHTML='<p class="placeholder-msg">No health records yet. Save a record above to see it here.</p>';return;}
  const iMap={'Routine Check':'ri-g lucide:check-circle','Illness Observation':'ri-o lucide:eye','Vet Visit':'ri-g lucide:stethoscope','Medication Given':'ri-r lucide:pill','Post-Surgery Observation':'ri-o lucide:eye','Injury Report':'ri-r lucide:alert-triangle'};
  container.innerHTML=healthLog.map((r,i)=>{
    const ic=(iMap[r.type]||'ri-g lucide:check-circle').split(' ');
    const extras=[];
    if(r.notes) extras.push(r.notes);
    if(r.treatment) extras.push(`<span style="color:var(--orange);font-weight:600;">Treatment:</span> ${r.treatment}`);
    if(r.next_checkup) extras.push(`<span style="color:var(--header);font-weight:600;">Next checkup:</span> ${r.next_checkup}`);
    if(r.worker_name) extras.push(`<span style="color:var(--sub);">Logged by:</span> ${r.worker_name}`);
    return`<div class="record-row"><div class="record-left"><div class="rec-icon ${ic[0]}"><span class="iconify" data-icon="${ic[1]}" data-width="17"></span></div><div><div class="rec-name">${r.type} — ${r.animal}</div><div class="rec-sub">${extras.join(' &nbsp;·&nbsp; ')||'No additional notes'}</div></div></div><div style="display:flex;align-items:center;gap:8px;flex-shrink:0;"><span class="rec-date">${r.dateStr}</span><div class="card-actions"><button class="icon-btn" title="Edit" onclick="editHealth(${i})"><span class="iconify" data-icon="lucide:pencil" data-width="13"></span></button><button class="icon-btn del" title="Delete" onclick="deleteHealth(${i})"><span class="iconify" data-icon="lucide:trash-2" data-width="13"></span></button></div></div></div>`;
  }).join('');
}
async function logHealth(){
  const animalVal=document.getElementById('healthAnimal').value;
  const type=document.getElementById('healthType').value;
  const notes=document.getElementById('healthNotes').value.trim();
  const treatment=document.getElementById('healthTreatment').value.trim();
  const nextCheckup=document.getElementById('healthNextCheckup').value||null;
  if(!animalVal){showToast('Please select an animal.','error');return;}
  const timeStr=new Date().toLocaleTimeString('en-MY',{hour:'2-digit',minute:'2-digit'});
  const animal=animals.find(a=>animalVal.startsWith(a.name));
  const entry={animal:animalVal.split(' ')[0],type,notes,treatment,next_checkup:nextCheckup,dateStr:'Today, '+timeStr};
  try{
    const res=await fetch('api/health_worker.php',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({animal_id:animal?animal.id:null,health_status:type,diagnosis:notes,treatment,next_checkup:nextCheckup})});
    const data=await res.json();
    if(data.success){ await loadHealthLog(); }
    else { healthLog.unshift(entry); LS.set('healthLog',healthLog); renderHealthRecords(); }
  }catch(e){ healthLog.unshift(entry); LS.set('healthLog',healthLog); healthCount=healthLog.length; LS.set('healthCount',healthCount); renderHealthRecords(); }
  pushNotif('🩺','ni-orange','Health event recorded',`${type} logged for ${entry.animal}`);
  document.getElementById('healthAnimal').value='';
  document.getElementById('healthNotes').value='';
  document.getElementById('healthTreatment').value='';
  document.getElementById('healthNextCheckup').value='';
  syncDashboard();showToast('🩺 Health record saved!');
}


// ─── VACCINATION ───
let vaxLog=LS.get('vaxLog',[]);
function renderVaxList(){
  const container=document.getElementById('vaxList');
  if(!vaxLog.length){container.innerHTML='<p class="placeholder-msg">No vaccination records yet. Save a record above to see it here.</p>';return;}
  container.innerHTML=vaxLog.map((r,i)=>`<div class="vax-item"><div class="vax-left"><div class="vax-icon"><span class="iconify" data-icon="lucide:syringe" data-width="17"></span></div><div><div class="vax-name">${r.animal} — ${r.name}</div><div class="vax-detail">${r.detail}</div>${r.loggedBy?`<div class="vax-detail" style="margin-top:2px;">Logged by: <strong>${r.loggedBy}</strong></div>`:''}</div></div><div style="display:flex;align-items:center;gap:8px;"><span class="vax-status vs-done">✓ Done</span><div class="card-actions"><button class="icon-btn" title="Edit" onclick="editVax(${i})"><span class="iconify" data-icon="lucide:pencil" data-width="13"></span></button><button class="icon-btn del" title="Delete" onclick="deleteVax(${i})"><span class="iconify" data-icon="lucide:trash-2" data-width="13"></span></button></div></div></div>`).join('');
}
// Load vaccinations from API
async function loadVaccinationsFromAPI() {
  try {
    const res = await fetch('api/vaccinations_worker.php', { credentials: 'include' });
    const data = await res.json();
    if (data.success) {
      vaxLog = data.vaccinations.map(v => ({
        animal: v.animal_name,
        name: v.vaccine_name,
        detail: `Given: ${new Date(v.date_given).toLocaleDateString('en-MY', { day:'numeric', month:'short', year:'numeric' })}${v.next_due_date ? ' · Next: ' + new Date(v.next_due_date).toLocaleDateString('en-MY', { day:'numeric', month:'short', year:'numeric' }) : ''}${v.vet_name ? ' · ' + v.vet_name : ''}`,
        rawDate: v.date_given,
        rawNext: v.next_due_date,
        vet: v.vet_name,
        loggedBy: v.worker_name || '',
        id: v.id
      }));
      LS.set('vaxLog', vaxLog);
      vaxCount = vaxLog.length;
      LS.set('vaxCount', vaxCount);
      renderVaxList();
      syncDashboard();
    }
  } catch(e) { console.warn(e); }
}

// Log vaccination via API
async function logVaccination() {
  const animalVal = document.getElementById('vaxAnimal').value;
  const name = document.getElementById('vaxName').value;
  const date = document.getElementById('vaxDate').value;
  const next = document.getElementById('vaxNext').value || null;
  const vet = document.getElementById('vaxVet').value.trim() || null;

  if (!animalVal || !date) { showToast('Animal and date given are required.', 'error'); return; }

  const animal = animals.find(a => animalVal.startsWith(a.name));
  if (!animal) { showToast('Invalid animal', 'error'); return; }

  const payload = {
    animal_id: animal.id,
    vaccine_name: name,
    date_given: date,
    next_due_date: next,
    vet_name: vet
  };

  try {
    const res = await fetch('api/vaccinations_worker.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast('💉 Vaccination recorded!');
      await loadVaccinationsFromAPI();
      document.getElementById('vaxAnimal').value = '';
      document.getElementById('vaxDate').value = '';
      document.getElementById('vaxNext').value = '';
      document.getElementById('vaxVet').value = '';
      syncDashboard();
    } else {
      showToast(data.message, 'error');
    }
  } catch(e) {
    showToast('Network error', 'error');
  }
}

// ─── TASKS ───
// Clear any stale localStorage task data (removes old hardcoded defaults)
localStorage.removeItem('wt_tasks');
localStorage.removeItem('wt_feedingCount');
localStorage.removeItem('wt_healthCount');
localStorage.removeItem('wt_vaxCount');
localStorage.removeItem('wt_incidentCount');
let tasks=[];
let taskNextId=1;
let taskFilter='all';
let editingTaskId=null;

function renderTasks(){
  const pc={high:'tp-h',med:'tp-m',low:'tp-l'},pl={high:'High',med:'Medium',low:'Low'};
  let filtered;
  if(taskFilter==='all') filtered=[...tasks];
  else if(taskFilter==='active') filtered=tasks.filter(t=>t.active&&!t.done);
  else if(taskFilter==='pending') filtered=tasks.filter(t=>t.active&&!t.done);
  else if(taskFilter==='done') filtered=tasks.filter(t=>t.done);
  else if(taskFilter==='inactive') filtered=tasks.filter(t=>!t.active);
  else filtered=[...tasks];

  const tl=document.getElementById('taskList');
  if(!filtered.length){
    tl.innerHTML=`<div class="empty-state" style="padding:30px 0;"><div class="empty-icon">📋</div><p>${
      taskFilter==='done'?'No completed tasks yet.':
      taskFilter==='inactive'?'No inactive tasks.':
      'No tasks match this filter.'
    }</p></div>`;
    updateTaskProgress();return;
  }
  tl.innerHTML=filtered.map(t=>`
    <div class="task-item ${!t.active?'inactive':''}">
      <div class="task-check ${t.done?'done':''} ${!t.active?'inactive-check':''}"
           onclick="${t.active&&!t.done?`toggleTask(${t.id})`:t.active&&t.done?`toggleTask(${t.id})`:''}"></div>
      <div class="task-text">
        <div class="task-name ${t.done?'done':''} ${!t.active?'inactive-name':''}">${t.name}</div>
        <div class="task-meta">${t.meta||''} · ${t.zone}${t.createdBy?' · Added by '+t.createdBy:''}${!t.active?' · <em>Inactive</em>':''}</div>
      </div>
      <span class="task-pri ${pc[t.priority]}">${pl[t.priority]}</span>
      <div class="task-actions">
        <button class="task-toggle-btn ${t.active?'active-task':''}" title="${t.active?'Deactivate task':'Activate task'}" onclick="toggleTaskActive(${t.id})">
          <span class="iconify" data-icon="${t.active?'lucide:toggle-right':'lucide:toggle-left'}" data-width="15"></span>
        </button>
        <button class="task-edit-btn" title="Edit task" onclick="openTaskModal(${t.id})">
          <span class="iconify" data-icon="lucide:pencil" data-width="13"></span>
        </button>
        <button class="task-del-btn" title="Delete task" onclick="deleteTask(${t.id})">
          <span class="iconify" data-icon="lucide:trash-2" data-width="13"></span>
        </button>
      </div>
    </div>`).join('');
  updateTaskProgress();
}

async function toggleTask(id) {
  const t = tasks.find(t => t.id === id);
  if (!t) return;
  try {
    const res = await fetch('api/dailytask_worker.php', {
      method: 'PUT',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, done: !t.done })
    });
    const data = await res.json();
    if (data.success) {
      if (!t.done) pushNotif('✅','ni-green','Task completed',`"${t.name}" marked as done.`);
      await loadTasksFromAPI();
      syncDashboard();
    } else {
      showToast(data.message, 'error');
    }
  } catch(e) { showToast('Network error', 'error'); }
}

async function toggleTaskActive(id) {
  const t = tasks.find(t => t.id === id);
  if (!t) return;
  const newActive = !t.active;
  try {
    const res = await fetch('api/dailytask_worker.php', {
      method: 'PUT',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, active: newActive, done: newActive ? false : false }) // deactivate also sets done=0
    });
    const data = await res.json();
    if (data.success) {
      showToast(newActive ? '✅ Task activated!' : '⏸️ Task deactivated.');
      await loadTasksFromAPI();
      syncDashboard();
    } else {
      showToast(data.message, 'error');
    }
  } catch(e) { showToast('Network error', 'error'); }
}

function openTaskModal(id=null){
  editingTaskId=id;
  if(id){
    const t=tasks.find(t=>t.id===id);
    document.getElementById('taskModalTitle').textContent='Edit Task';
    document.getElementById('taskModalSaveLbl').textContent='Save Changes';
    document.getElementById('tm_name').value=t.name;
    document.getElementById('tm_meta').value=t.meta||'';
    document.getElementById('tm_priority').value=t.priority;
    document.getElementById('tm_zone').value=t.zone;
    document.getElementById('tm_active').value=String(t.active!==false);
  }else{
    document.getElementById('taskModalTitle').textContent='Add New Task';
    document.getElementById('taskModalSaveLbl').textContent='Add Task';
    document.getElementById('tm_name').value='';
    document.getElementById('tm_meta').value='';
    document.getElementById('tm_priority').value='med';
    document.getElementById('tm_zone').value='Zone A';
    document.getElementById('tm_active').value='true';
  }
  document.getElementById('taskModal').classList.add('open');
}

async function saveTask() {
  const name = document.getElementById('tm_name').value.trim();
  if (!name) { showToast('Please enter a task description.', 'error'); return; }
  const meta = document.getElementById('tm_meta').value.trim();
  const priority = document.getElementById('tm_priority').value;
  const zone = document.getElementById('tm_zone').value;
  const active = document.getElementById('tm_active').value === 'true';

  const payload = {
    name,
    meta: meta || 'No details',
    zone,
    priority,
    active
  };

  let method = 'POST';
  let url = 'api/dailytask_worker.php';
  if (editingTaskId) {
    method = 'PUT';
    payload.id = editingTaskId;
  }

  try {
    const res = await fetch(url, {
      method,
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast(editingTaskId ? '✏️ Task updated!' : '📋 Task added!');
      await loadTasksFromAPI();
      closeModal('taskModal');
      syncDashboard();
    } else {
      showToast(data.message, 'error');
    }
  } catch(e) {
    showToast('Network error', 'error');
  }
}

async function deleteTask(id) {
  if (!confirm('Delete this task?')) return;
  try {
    const res = await fetch('api/dailytask_worker.php', {
      method: 'DELETE',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('🗑️ Task deleted.');
      await loadTasksFromAPI();
      syncDashboard();
    } else {
      showToast(data.message, 'error');
    }
  } catch(e) {
    showToast('Network error', 'error');
  }
}

function filterTasks(f, btn) {
  taskFilter = f;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadTasksFromAPI();   // reload with new filter
}

function updateTaskProgress(){
  const activeTasks=tasks.filter(t=>t.active);
  const done=activeTasks.filter(t=>t.done).length;
  const total=activeTasks.length;
  const pct=total?Math.round(done/total*100):0;
  document.getElementById('taskDoneLabel').textContent=`${done} of ${total} active tasks completed`;
  document.getElementById('taskPctLabel').textContent=`${pct}%`;
  document.getElementById('taskBar').style.width=pct+'%';
  document.getElementById('dashTaskCount').textContent=total;
  document.getElementById('dashTaskChange').textContent=done+' done';
  document.getElementById('dashTaskBadge').textContent=(total-done)+' Remaining';
  document.getElementById('sideTaskBadge').textContent=(total-done);
}

// ─── INCIDENTS ───
let incidentLog=LS.get('incidentLog',[]);
let selSev='low';
function renderIncidents(){
  const container=document.getElementById('incidentHistory');
  if(!incidentLog.length){container.innerHTML='<p class="placeholder-msg">No incidents reported yet. Submit a report above if needed.</p>';return;}
  container.innerHTML=incidentLog.map((r,i)=>`<div class="inc-row"><div class="inc-dot-col"><div class="inc-dot" style="background:${r.dotC};"></div><div class="inc-line"></div></div><div style="flex:1;"><div class="inc-title">${r.title}</div><div class="inc-desc">${r.desc}</div><div class="inc-meta">📅 ${r.meta}</div></div><div class="card-actions" style="flex-shrink:0;padding-top:2px;"><button class="icon-btn" title="Edit" onclick="editIncident(${i})"><span class="iconify" data-icon="lucide:pencil" data-width="13"></span></button><button class="icon-btn del" title="Delete" onclick="deleteIncident(${i})"><span class="iconify" data-icon="lucide:trash-2" data-width="13"></span></button></div></div>`).join('');
}
function pickSev(el,s){document.querySelectorAll('.sev-opt').forEach(o=>o.classList.remove('sel'));el.classList.add('sel');selSev=s;}
// Load incidents from API on page load
async function loadIncidentsFromAPI() {
  try {
    const res = await fetch('api/incident_worker.php', { credentials: 'include' });
    const data = await res.json();
    if (data.success) {
      incidentLog = data.incidents.map(inc => ({
        dotC: inc.severity === 'high' ? 'var(--red)' : (inc.severity === 'medium' ? 'var(--orange)' : '#2e8b77'),
        title: `${inc.incident_type}${inc.animal_name ? ' — ' + inc.animal_name : ''} (${inc.severity})`,
        desc: inc.description,
        meta: new Date(inc.reported_at).toLocaleString('en-MY', { hour:'2-digit', minute:'2-digit', day:'numeric', month:'short', year:'numeric' })
              + (inc.reported_by_name ? ' · Reported by ' + inc.reported_by_name : ''),
        id: inc.id
      }));
      LS.set('incidentLog', incidentLog);
      incidentCount = incidentLog.length;
      LS.set('incidentCount', incidentCount);
      renderIncidents();
      syncDashboard();
    }
  } catch(e) { console.warn(e); }
}

// Submit incident to API
async function submitIncident() {
  const type = document.getElementById('incType').value;
  const desc = document.getElementById('incDesc').value.trim();
  const animalVal = document.getElementById('incAnimal').value;
  if (!desc) { showToast('Please describe the incident.', 'error'); return; }

  let animalId = null;
  if (animalVal && animalVal !== 'N/A — Worker Only') {
    const animal = animals.find(a => animalVal.startsWith(a.name));
    if (animal) animalId = animal.id;
  }

  const payload = {
    incident_type: type,
    description: desc,
    animal_id: animalId,
    severity: selSev
  };

  try {
    const res = await fetch('api/incident_worker.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast('🚨 Incident report submitted!');
      await loadIncidentsFromAPI();   // refresh list
      document.getElementById('incAnimal').value = '';
      document.getElementById('incDesc').value = '';
      syncDashboard();
    } else {
      showToast(data.message, 'error');
    }
  } catch (e) {
    showToast('Network error', 'error');
  }
}

// ─── MODAL HELPER ───
function closeModal(id){document.getElementById(id).classList.remove('open');}

// ─── FEEDING EDIT / DELETE ───
let editFeedIdx=null;
function editFeeding(i){editFeedIdx=i;const r=feedingLog[i];document.getElementById('ef_type').value=r.type;document.getElementById('ef_qty').value=r.qty;document.getElementById('ef_consumed').value=r.consumed;document.getElementById('ef_notes').value=r.notes||'';document.getElementById('editFeedModal').classList.add('open');}
async function saveEditFeeding(){
  const r=feedingLog[editFeedIdx];
  r.type=document.getElementById('ef_type').value;
  r.qty=parseFloat(document.getElementById('ef_qty').value).toFixed(1);
  r.consumed=document.getElementById('ef_consumed').value;
  r.notes=document.getElementById('ef_notes').value.trim();
  try{ await fetch('api/feeding_worker.php',{method:'PUT',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id,food_type:r.type,quantity:r.qty,consumed:r.consumed,notes:r.notes})}); }catch(e){}
  LS.set('feedingLog',feedingLog); closeModal('editFeedModal');
  await loadFeedingLog(); showToast('✏️ Feeding record updated!');
}
async function deleteFeeding(i){
  if(!confirm('Delete this feeding record?'))return;
  const r=feedingLog[i];
  try{ await fetch('api/feeding_worker.php',{method:'DELETE',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id})}); }catch(e){}
  await loadFeedingLog(); syncDashboard(); showToast('🗑️ Feeding record deleted.');
}

// ─── FEEDING HISTORY ───
let feedingHistory=[];
async function loadFeedingHistory(){
  const dateFilter=document.getElementById('historyDateFilter')?document.getElementById('historyDateFilter').value:'';
  const url='api/feeding_worker.php?mode=history'+(dateFilter?'&date='+encodeURIComponent(dateFilter):'');
  try{
    const res=await fetch(url,{credentials:'include'});
    const data=await res.json();
    feedingHistory=data.success?data.records:[];
  }catch(e){feedingHistory=[];}
  renderFeedingHistory();
}
function clearHistoryFilter(){document.getElementById('historyDateFilter').value='';loadFeedingHistory();}
function renderFeedingHistory(){
  const tbody=document.getElementById('feedingHistoryBody');
  if(!tbody)return;
  if(!feedingHistory.length){tbody.innerHTML='<tr class="table-empty-row"><td colspan="8">No past feeding records found.</td></tr>';return;}
  const bc={'All Eaten':'b-green','Refused Food':'b-red','75% Eaten':'b-orange','50% Eaten':'b-orange','25% Eaten':'b-red'};
  tbody.innerHTML=feedingHistory.map(r=>`<tr>
    <td style="white-space:nowrap;font-weight:500;">${r.date}</td>
    <td>${r.time}</td><td>${r.animal}</td><td>${r.type}</td><td>${r.qty}</td>
    <td><span class="badge ${bc[r.consumed]||'b-orange'}">${r.consumed}</span></td>
    <td style="color:var(--sub);font-size:12px;">${r.notes||'—'}</td>
    <td>${r.worker||'—'}</td>
  </tr>`).join('');
}

// ─── HEALTH EDIT / DELETE ───
let editHealthIdx=null;
function editHealth(i){
  editHealthIdx=i;
  const r=healthLog[i];
  document.getElementById('eh_type').value=r.type;
  document.getElementById('eh_notes').value=r.notes||'';
  document.getElementById('eh_treatment').value=r.treatment||'';
  // next_checkup from API is formatted like "12 Apr 2025", need raw date for input
  document.getElementById('eh_next_checkup').value=r.rawNextCheckup||'';
  document.getElementById('editHealthModal').classList.add('open');
}
async function saveEditHealth(){
  const r=healthLog[editHealthIdx];
  r.type=document.getElementById('eh_type').value;
  r.notes=document.getElementById('eh_notes').value.trim();
  r.treatment=document.getElementById('eh_treatment').value.trim();
  const rawNext=document.getElementById('eh_next_checkup').value;
  r.rawNextCheckup=rawNext;
  r.next_checkup=rawNext?new Date(rawNext+'T00:00:00').toLocaleDateString('en-MY',{day:'numeric',month:'short',year:'numeric'}):'';
  try{ await fetch('api/health_worker.php',{method:'PUT',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id,health_status:r.type,diagnosis:r.notes,treatment:r.treatment,next_checkup:rawNext||null})}); }catch(e){}
  LS.set('healthLog',healthLog); closeModal('editHealthModal');
  await loadHealthLog(); showToast('✏️ Health record updated!');
}
async function deleteHealth(i){
  if(!confirm('Delete this health record?'))return;
  const r=healthLog[i];
  try{ await fetch('api/health_worker.php',{method:'DELETE',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id})}); }catch(e){}
  await loadHealthLog(); syncDashboard(); showToast('🗑️ Health record deleted.');
}

// ─── VAX EDIT / DELETE ───
let editVaxIdx=null;
function editVax(i){editVaxIdx=i;const r=vaxLog[i];document.getElementById('ev_name').value=r.name;document.getElementById('ev_date').value=r.rawDate||'';document.getElementById('ev_next').value=r.rawNext||'';document.getElementById('ev_vet').value=r.vet||'';document.getElementById('editVaxModal').classList.add('open');}
async function saveEditVax(){
  const fmtDate=d=>d?new Date(d+'T00:00:00').toLocaleDateString('en-MY',{day:'numeric',month:'short',year:'numeric'}):'';
  const r=vaxLog[editVaxIdx];
  r.name=document.getElementById('ev_name').value;
  r.rawDate=document.getElementById('ev_date').value;
  r.rawNext=document.getElementById('ev_next').value;
  r.vet=document.getElementById('ev_vet').value.trim();
  r.detail=`Given: ${fmtDate(r.rawDate)}${r.rawNext?' · Next: '+fmtDate(r.rawNext):''}${r.vet?' · '+r.vet:''}`;
  try{ await fetch('api/vaccinations_worker.php',{method:'PUT',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id,vaccine_name:r.name,date_given:r.rawDate,next_due_date:r.rawNext||null,vet_name:r.vet||null})}); }catch(e){}
  LS.set('vaxLog',vaxLog); closeModal('editVaxModal');
  await loadVaccinationsFromAPI(); showToast('✏️ Vaccination record updated!');
}
async function deleteVax(i){
  if(!confirm('Delete this vaccination record?'))return;
  const r=vaxLog[i];
  try{ await fetch('api/vaccinations_worker.php',{method:'DELETE',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id})}); }catch(e){}
  await loadVaccinationsFromAPI(); syncDashboard(); showToast('🗑️ Vaccination record deleted.');
}

// ─── INCIDENT EDIT / DELETE ───
let editIncIdx=null;
function editIncident(i){editIncIdx=i;document.getElementById('ei_desc').value=incidentLog[i].desc;document.getElementById('editIncModal').classList.add('open');}
async function saveEditIncident(){
  const r=incidentLog[editIncIdx];
  r.desc=document.getElementById('ei_desc').value.trim();
  try{ await fetch('api/incident_worker.php',{method:'PUT',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id,description:r.desc})}); }catch(e){}
  LS.set('incidentLog',incidentLog); closeModal('editIncModal');
  await loadIncidentsFromAPI(); showToast('✏️ Incident report updated!');
}
async function deleteIncident(i){
  if(!confirm('Delete this incident report?'))return;
  const r=incidentLog[i];
  try{ await fetch('api/incident_worker.php',{method:'DELETE',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:r.id})}); }catch(e){}
  await loadIncidentsFromAPI(); syncDashboard(); showToast('🗑️ Incident report deleted.');
}

async function loadTasksFromAPI() {
  // 'pending' is a UI-only alias for active+not-done; API uses 'active'
  const apiFilter = taskFilter === 'pending' ? 'active' : taskFilter;
  try {
    const res = await fetch(`api/dailytask_worker.php?filter=${apiFilter}`, { credentials: 'include' });
    const data = await res.json();
    if (data.success) {
      tasks = data.tasks.map(t => ({
        id: t.id,
        name: t.name,
        meta: t.meta,
        zone: t.zone,
        priority: t.priority,
        done: t.done,
        active: t.active,
        createdBy: t.created_by_name || ''
      }));
      LS.set('tasks', tasks);
      renderTasks();
      syncDashboard();
    }
  } catch(e) { console.warn(e); }
}

// ─── BOOT ───
// All data loading happens inside initSession() after auth is confirmed.
// initSession → loadAnimals → loadFeedingLog + loadHealthLog
//             → loadIncidentsFromAPI → loadVaccinationsFromAPI → loadTasksFromAPI
_updateNotifBadge();
loadFeedingHistory();
initSession();
</script>
</body>
</html>