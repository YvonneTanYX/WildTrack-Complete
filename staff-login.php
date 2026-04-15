<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WildTrack — Staff Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --forest:#e8f5e9; --moss:#c8e6c9; --fern:#2a7a40; --sage:#3daa58;
      --sage-dk:#1e6b30; --mist:#1b5e20; --white:#ffffff;
      --error:#e53935; --text:#1a2e1e; --text-sub:#3d6b44;
    }
    html,body { min-height:100%; font-family:'DM Sans',sans-serif; background:var(--forest); color:var(--text); overflow-x:hidden; }

    .bg { position:fixed;inset:0;z-index:0;
      background:radial-gradient(ellipse 70% 55% at 10% 20%,rgba(42,122,64,.25) 0%,transparent 60%),
                 radial-gradient(ellipse 60% 70% at 90% 80%,rgba(30,107,48,.18) 0%,transparent 60%),
                 linear-gradient(160deg,#c8e6c9 0%,#e8f5e9 40%,#a5d6a7 100%); }

    .blob { position:fixed;border-radius:50%;pointer-events:none;z-index:0;animation:blobMove ease-in-out infinite alternate; }
    .blob1{width:420px;height:420px;background:rgba(42,122,64,.15);top:-80px;left:-100px;animation-duration:12s;}
    .blob2{width:320px;height:320px;background:rgba(30,107,48,.12);top:30%;right:-60px;animation-duration:15s;animation-delay:-4s;}
    .blob3{width:260px;height:260px;background:rgba(42,122,64,.10);bottom:5%;left:10%;animation-duration:18s;animation-delay:-8s;}
    @keyframes blobMove{0%{transform:translate(0,0) scale(1);}33%{transform:translate(30px,-20px) scale(1.06);}66%{transform:translate(-20px,30px) scale(.95);}100%{transform:translate(15px,15px) scale(1.03);}}

    .wave-bg{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;}
    .wave-bg svg{position:absolute;width:100%;height:100%;}

    .page{position:relative;z-index:2;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}

    .card{width:100%;max-width:440px;background:rgba(255,255,255,.85);backdrop-filter:blur(24px) saturate(1.6);
      border:1.5px solid rgba(42,122,64,.25);border-radius:24px;padding:2.4rem 2.4rem 2rem;
      box-shadow:0 8px 40px rgba(42,122,64,.16),0 2px 12px rgba(42,122,64,.08),0 0 0 1px rgba(255,255,255,.9) inset;
      animation:slideUp .65s cubic-bezier(.22,1,.36,1) both;}
    @keyframes slideUp{from{opacity:0;transform:translateY(32px);}to{opacity:1;transform:none;}}

    /* Brand */
    .brand{display:flex;align-items:center;gap:.9rem;margin-bottom:1.8rem;}
    .brand-logo{width:58px;height:58px;border-radius:14px;object-fit:contain;background:#fff;box-shadow:0 3px 14px rgba(42,122,64,.2);padding:3px;}
    .brand-name{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--mist);letter-spacing:.02em;line-height:1.1;}
    .brand-sub{font-size:.67rem;font-weight:600;color:var(--text-sub);letter-spacing:.14em;text-transform:uppercase;margin-top:3px;}
    .brand-est{font-size:.62rem;color:#888;letter-spacing:.06em;margin-top:1px;}

    /* Staff badge */
    .staff-badge{display:inline-flex;align-items:center;gap:.4rem;font-size:.7rem;font-weight:700;
      letter-spacing:.1em;text-transform:uppercase;padding:.28rem .8rem;border-radius:20px;margin-bottom:1rem;
      background:rgba(42,122,64,.12);color:var(--sage-dk);border:1px solid rgba(42,122,64,.3);}

    /* Security notice */
    .security-notice{background:rgba(42,122,64,.07);border:1px solid rgba(42,122,64,.2);border-radius:10px;
      padding:.65rem 1rem;font-size:.75rem;color:var(--text-sub);margin-bottom:1.2rem;
      display:flex;align-items:center;gap:.5rem;line-height:1.4;}

    .section-title{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:600;color:var(--mist);margin-bottom:.3rem;}
    .section-sub{font-size:.78rem;color:var(--text-sub);margin-bottom:1.3rem;line-height:1.5;}

    .field{margin-bottom:.9rem;}
    label{display:block;font-size:.73rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-sub);margin-bottom:.38rem;}
    input[type=email],input[type=password]{
      width:100%;background:rgba(255,255,255,.85);border:1.5px solid rgba(42,122,64,.2);border-radius:9px;
      padding:.7rem 1rem;font-family:'DM Sans',sans-serif;font-size:.9rem;color:var(--text);outline:none;
      transition:border .2s,background .2s,box-shadow .2s;}
    input::placeholder{color:rgba(42,80,50,.38);}
    input:focus{border-color:var(--sage);background:#fff;box-shadow:0 0 0 3px rgba(61,170,88,.14);}
    input.error-field{border-color:var(--error);}

    .pw-wrap{position:relative;}
    .pw-wrap input{padding-right:2.8rem;}
    .pw-toggle{position:absolute;right:.9rem;top:50%;transform:translateY(-50%);background:none;border:none;
      cursor:pointer;color:var(--text-sub);font-size:1rem;padding:0;line-height:1;transition:color .2s;}
    .pw-toggle:hover{color:var(--fern);}

    .err-msg{font-size:.74rem;color:var(--error);margin-top:.28rem;display:none;}
    .err-msg.show{display:block;}

    .forgot-row{text-align:right;margin-top:-.4rem;margin-bottom:.9rem;}
    .forgot-row a{font-size:.75rem;color:var(--text-sub);text-decoration:none;transition:color .2s;}
    .forgot-row a:hover{color:var(--fern);}

    .btn-submit{width:100%;border:none;border-radius:10px;padding:.84rem;font-family:'DM Sans',sans-serif;
      font-size:.93rem;font-weight:600;color:#fff;cursor:pointer;margin-top:.4rem;
      background:linear-gradient(135deg,var(--fern),var(--sage));box-shadow:0 4px 18px rgba(42,122,64,.35);
      transition:transform .15s,filter .15s;position:relative;overflow:hidden;}
    .btn-submit::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.15),transparent);border-radius:inherit;}
    .btn-submit:hover{transform:translateY(-1px);filter:brightness(1.06);}
    .btn-submit.loading{pointer-events:none;filter:brightness(.8);}
    .btn-submit .spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto;}
    .btn-submit.loading .btn-label{display:none;}
    .btn-submit.loading .spinner{display:block;}
    @keyframes spin{to{transform:rotate(360deg);}}

    /* Change password step */
    .step{display:none;}
    .step.active{display:block;animation:fadeIn .3s ease;}
    @keyframes fadeIn{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:none;}}
    .back-row{margin-bottom:1.1rem;}
    .back-btn{background:none;border:none;cursor:pointer;color:var(--text-sub);font-size:.78rem;
      font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.3rem;padding:0;transition:color .2s;}
    .back-btn:hover{color:var(--fern);}

    /* Toast */
    .toast{position:fixed;top:1.4rem;right:1.4rem;z-index:99;background:#fff;border:1.5px solid rgba(42,122,64,.3);
      border-radius:12px;padding:.85rem 1.2rem;display:flex;align-items:center;gap:.6rem;font-size:.85rem;color:var(--mist);
      box-shadow:0 8px 30px rgba(42,122,64,.15);transform:translateX(130%);transition:transform .4s cubic-bezier(.22,1,.36,1);}
    .toast.show{transform:translateX(0);}

    /* Forgot modal */
    .modal-overlay{position:fixed;inset:0;z-index:50;background:rgba(30,60,35,.35);backdrop-filter:blur(6px);
      display:none;align-items:center;justify-content:center;padding:1.5rem;}
    .modal-overlay.open{display:flex;}
    .modal{background:#fff;border:1.5px solid rgba(42,122,64,.2);border-radius:18px;padding:2rem;width:100%;max-width:380px;
      animation:slideUp .35s ease;box-shadow:0 16px 48px rgba(42,122,64,.15);}
    .modal-title{font-family:'Playfair Display',serif;font-size:1.15rem;color:var(--mist);margin-bottom:.3rem;}
    .modal-sub{font-size:.78rem;color:var(--text-sub);margin-bottom:1.2rem;}
    .modal-actions{display:flex;gap:.6rem;margin-top:1.1rem;}
    .btn-ghost{flex:1;padding:.65rem;background:rgba(42,122,64,.06);border:1px solid rgba(42,122,64,.2);border-radius:8px;
      font-family:'DM Sans',sans-serif;font-size:.83rem;color:var(--text-sub);cursor:pointer;transition:all .2s;}
    .btn-ghost:hover{background:rgba(42,122,64,.12);color:var(--fern);}
    .btn-primary-sm{flex:1;padding:.65rem;background:var(--fern);border:none;border-radius:8px;
      font-family:'DM Sans',sans-serif;font-size:.83rem;color:#fff;cursor:pointer;transition:filter .2s;}
    .btn-primary-sm:hover{filter:brightness(1.1);}
    .btn-primary-sm:disabled{opacity:.65;cursor:not-allowed;filter:none;}

    @media(max-height:720px){.page{align-items:flex-start;padding-top:1rem;}html,body{overflow-y:auto;}}
  </style>
</head>
<body>

<div class="bg"></div>
<div class="blob blob1"></div><div class="blob blob2"></div><div class="blob blob3"></div>

<div class="wave-bg">
  <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <path d="M0,200 C200,120 400,280 600,200 C800,120 1000,260 1200,200 C1320,160 1380,180 1440,200" fill="none" stroke="rgba(42,122,64,.09)" stroke-width="2.5"/>
    <path d="M0,400 C180,320 360,460 540,400 C720,340 900,460 1080,400 C1260,340 1380,370 1440,400" fill="none" stroke="rgba(42,122,64,.07)" stroke-width="2"/>
    <path d="M0,600 C220,530 440,640 660,600 C880,560 1100,640 1320,600 C1380,585 1420,592 1440,600" fill="none" stroke="rgba(42,122,64,.06)" stroke-width="1.5"/>
  </svg>
</div>

<div class="page">
<div class="card">

  <div class="brand">
    <img src="WildTrack Logo.png" alt="WildTrack Logo" class="brand-logo" onerror="this.style.display='none'">
    <div>
      <div class="brand-name">WildTrack</div>
      <div class="brand-sub">Malaysia</div>
      <div class="brand-est">Est. 2026</div>
    </div>
  </div>

  <!-- ══ STEP: Staff Login (default) ══ -->
  <div id="step-login">
    <div class="staff-badge">🐯 Staff Portal</div>
    <div class="security-notice">
      <span>🔒</span>
      <span>Authorised personnel only. This page is not linked publicly.</span>
    </div>
    <div class="section-title">Staff Sign In</div>
    <div class="section-sub">Access your WildTrack staff dashboard.</div>

    <div class="field">
      <label for="sl-email">Work Email</label>
      <input type="email" id="sl-email" placeholder="you@wildtrack.org" autocomplete="username"/>
      <div class="err-msg" id="err-sl-email">Please enter a valid email.</div>
    </div>
    <div class="field">
      <label for="sl-pw">Password</label>
      <div class="pw-wrap">
        <input type="password" id="sl-pw" placeholder="••••••••" autocomplete="current-password"/>
        <button class="pw-toggle" type="button" onclick="togglePw('sl-pw',this)">👁</button>
      </div>
      <div class="err-msg" id="err-sl-pw">Password is required.</div>
    </div>
    <div class="forgot-row"><a href="#" onclick="openModal();return false;">Forgot password?</a></div>
    <button class="btn-submit" id="btn-slogin" onclick="handleStaffLogin()">
      <span class="btn-label">Sign In</span><div class="spinner"></div>
    </button>
  </div>

  <!-- ══ STEP: First-login password change ══ -->
  <div class="step" id="step-changepassword">
    <div class="back-row"><button class="back-btn" onclick="showLoginStep()">← Back</button></div>
    <div style="text-align:center;margin-bottom:1.4rem;">
      <div style="font-size:2rem;margin-bottom:.5rem;">🔐</div>
      <div class="section-title">Set Your Password</div>
      <div class="section-sub">Your account was created by an administrator.<br/>Please set your own password before continuing.</div>
    </div>
    <div class="field">
      <label for="cp-pw">New Password</label>
      <div class="pw-wrap">
        <input type="password" id="cp-pw" placeholder="At least 8 characters"/>
        <button class="pw-toggle" type="button" onclick="togglePw('cp-pw',this)">👁</button>
      </div>
      <div class="err-msg" id="err-cp-pw">Password must be at least 8 characters.</div>
    </div>
    <div class="field">
      <label for="cp-pw2">Confirm New Password</label>
      <div class="pw-wrap">
        <input type="password" id="cp-pw2" placeholder="Repeat password"/>
        <button class="pw-toggle" type="button" onclick="togglePw('cp-pw2',this)">👁</button>
      </div>
      <div class="err-msg" id="err-cp-pw2">Passwords do not match.</div>
    </div>
    <button class="btn-submit" id="btn-changepw" onclick="handleChangePassword()">
      <span class="btn-label">Set Password &amp; Continue</span><div class="spinner"></div>
    </button>
  </div>

</div><!-- /card -->
</div><!-- /page -->

<!-- Forgot password modal -->
<div class="modal-overlay" id="forgotModal">
  <div class="modal">
    <div class="modal-title">Reset Password</div>
    <div class="modal-sub">Enter your registered email and we'll send a secure reset link valid for 1 hour.</div>
    <div class="field">
      <label for="reset-email">Email Address</label>
      <input type="email" id="reset-email" placeholder="you@wildtrack.org"/>
      <div id="err-reset-email" style="font-size:.74rem;color:#e53935;margin-top:.28rem;display:none;">
        Please enter a valid email address.
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn-primary-sm" id="btn-send-reset" onclick="sendReset()">Send Link</button>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <span id="toast-icon">✅</span>
  <span id="toast-msg"></span>
</div>

<script>
  /* ── Helpers ── */
  function togglePw(id,btn){var i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
  function showErr(id,v){var e=document.getElementById(id);if(e)e.classList.toggle('show',v);}
  function markField(id,v){var e=document.getElementById(id);if(e)e.classList.toggle('error-field',v);}
  function clearErrors(){document.querySelectorAll('.err-msg').forEach(function(e){e.classList.remove('show');});document.querySelectorAll('.error-field').forEach(function(e){e.classList.remove('error-field');});}
  function isEmail(v){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);}
  function setLoading(id,v){document.getElementById(id).classList.toggle('loading',v);}
  function showToast(msg,icon){
    icon=icon||'✅';
    document.getElementById('toast-msg').textContent=msg;
    document.getElementById('toast-icon').textContent=icon;
    var t=document.getElementById('toast');t.classList.add('show');
    setTimeout(function(){t.classList.remove('show');},3500);
  }

  function showLoginStep() {
    document.getElementById('step-login').style.display = '';
    document.getElementById('step-changepassword').classList.remove('active');
    clearErrors();
  }

  /* ── Staff Login ── */
  async function handleStaffLogin() {
    clearErrors(); var ok = true;
    var email = document.getElementById('sl-email').value.trim();
    var pw    = document.getElementById('sl-pw').value;
    if (!isEmail(email)) { showErr('err-sl-email',true); markField('sl-email',true); ok=false; }
    if (!pw)              { showErr('err-sl-pw',true);   markField('sl-pw',true);    ok=false; }
    if (!ok) return;
    setLoading('btn-slogin',true);
    try {
      var res  = await fetch('http://localhost/WildTrack/api/auth.php?action=login', {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({email:email, password:pw})
      });
      var data = await res.json();
      if (data.success) {
        var role = data.user.role;
        if (role !== 'admin' && role !== 'worker') {
          showToast('This is a visitor account. Please use the visitor login.','❌');
          setLoading('btn-slogin',false); return;
        }
        if (data.user.must_change_pw) {
          sessionStorage.setItem('pendingRole', role);
          document.getElementById('step-login').style.display = 'none';
          document.getElementById('step-changepassword').classList.add('active');
          setLoading('btn-slogin',false); return;
        }
        if (role === 'admin') {
          showToast('Welcome, Admin! Redirecting… 🛡️');
          setTimeout(function(){window.location.href='admin.php';}, 1200);
        } else {
          showToast('Welcome! Redirecting to staff portal… 🐾');
          setTimeout(function(){window.location.href='mainpageworker.php';}, 1200);
        }
      } else {
        showToast(data.message,'❌'); setLoading('btn-slogin',false);
      }
    } catch(e) { showToast('Cannot connect to server.','❌'); setLoading('btn-slogin',false); }
  }

  /* ── Change Password (first login) ── */
  async function handleChangePassword() {
    clearErrors(); var ok = true;
    var pw  = document.getElementById('cp-pw').value;
    var pw2 = document.getElementById('cp-pw2').value;
    if (pw.length < 8) { showErr('err-cp-pw',true);  ok=false; }
    if (pw !== pw2)    { showErr('err-cp-pw2',true); ok=false; }
    if (!ok) return;
    setLoading('btn-changepw',true);
    try {
      var res  = await fetch('http://localhost/WildTrack/api/auth.php?action=change_password', {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({new_password:pw, confirm_password:pw2})
      });
      var data = await res.json();
      if (data.success) {
        showToast('Password set! Redirecting… 🐾');
        var role = sessionStorage.getItem('pendingRole');
        sessionStorage.removeItem('pendingRole');
        setTimeout(function(){
          window.location.href = role === 'admin' ? 'admin.php' : 'mainpageworker.php';
        }, 1200);
      } else {
        showToast(data.message,'❌'); setLoading('btn-changepw',false);
      }
    } catch(e) { showToast('Cannot connect to server.','❌'); setLoading('btn-changepw',false); }
  }

  /* ── Forgot password modal ── */
  function openModal() {
    document.getElementById('forgotModal').classList.add('open');
    setTimeout(function(){ document.getElementById('reset-email').focus(); }, 100);
  }
  function closeModal() {
    document.getElementById('forgotModal').classList.remove('open');
    document.getElementById('reset-email').value = '';
    var e = document.getElementById('err-reset-email'); if (e) e.style.display = 'none';
    var i = document.getElementById('reset-email');     if (i) i.style.borderColor = '';
  }
  function sendReset() {
    var email = document.getElementById('reset-email').value.trim();
    var errEl = document.getElementById('err-reset-email');
    var inp   = document.getElementById('reset-email');
    if (!isEmail(email)) {
      if (errEl) errEl.style.display = 'block';
      if (inp) inp.style.borderColor = '#e53935';
      inp.focus(); return;
    }
    if (errEl) errEl.style.display = 'none';
    if (inp) inp.style.borderColor = '';
    var btn = document.getElementById('btn-send-reset');
    var orig = btn.textContent;
    btn.textContent = 'Sending…'; btn.disabled = true;
    fetch('http://localhost/WildTrack/api/auth.php?action=forgot_password', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email })
    })
    .then(function(r){ if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    .then(function(data){
      closeModal(); btn.textContent = orig; btn.disabled = false;
      if (data.reset_link) {
        var result = confirm('Reset link generated!\n\nClick OK to open the reset page.\n\nLink: ' + data.reset_link);
        if (result) window.open(data.reset_link, '_blank');
        showToast('Reset link generated!', '🔗');
      } else if (data.success) {
        showToast(data.message, '✅');
      } else {
        showToast(data.message || 'Something went wrong.', '❌');
      }
    })
    .catch(function(err){
      btn.textContent = orig; btn.disabled = false;
      showToast('Cannot connect to server. ' + err.message, '❌');
    });
  }

  document.getElementById('reset-email').addEventListener('keydown', function(e){ if (e.key==='Enter') sendReset(); });
  document.getElementById('forgotModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
