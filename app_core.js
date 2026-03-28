function applyGlobalColorTheme(mood){
  const all=['mood-happy-ui','mood-calm-ui','mood-sad-ui','mood-anxious-ui','mood-angry-ui','mood-neutral-ui'];
  document.body.classList.remove(...all);
  const cls='mood-'+(mood||'neutral')+'-ui';
  if(all.includes(cls))document.body.classList.add(cls);
  const colors={sad:'#d97706',anxious:'#16a34a',angry:'#2563eb',happy:'#7c3aed',calm:'#e11d48',neutral:'#ea580c'};
  const c=colors[mood]||'#16a34a';
  document.documentElement.style.setProperty('--primary',c);
  document.documentElement.style.setProperty('--primary-light',c+'1a');
}
async function updateWebsiteColorsFromLatestMood(){
  try {
    const m = await fetchMoods();
    if (m.length > 0) {
      window._latestMood = m[0].mood;
      applyGlobalColorTheme(m[0].mood);
    } else {
      // New user — no mood logged yet, keep default white/light background
      window._latestMood = null;
      const all = ['mood-happy-ui','mood-calm-ui','mood-sad-ui','mood-anxious-ui','mood-angry-ui','mood-neutral-ui'];
      document.body.classList.remove(...all);
      document.documentElement.style.removeProperty('--primary');
      document.documentElement.style.removeProperty('--primary-light');
    }
  } catch { applyGlobalColorTheme('neutral'); }
}

async function fetchMoods(){
  try{const r=await fetch('get_moods.php');const t=await r.text();let d;try{d=JSON.parse(t);}catch(e){return[];}if(d&&d.error)return[];return Array.isArray(d)?d:[];}
  catch(e){return[];}
}
async function fetchJournal(){
  try{const r=await fetch('get_journal.php');const d=await r.json();return Array.isArray(d)?d:[];}
  catch(e){return[];}
}
function uuid(){return crypto.randomUUID?crypto.randomUUID():Math.random().toString(36).substr(2,9);}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function getBotResponse(msg){
  const l=msg.toLowerCase();let cat='default';
  if(/\b(hi|hello|hey)\b/.test(l))cat='greeting';
  else if(/\b(sad|down|depressed|cry|lonely)\b/.test(l))cat='sad';
  else if(/\b(anxious|nervous|worried|panic|stress)\b/.test(l))cat='anxious';
  else if(/\b(angry|mad|furious|frustrated)\b/.test(l))cat='angry';
  else if(/\b(happy|great|good|wonderful|excited)\b/.test(l))cat='happy';
  const r=BOT_RESPONSES[cat];return r[Math.floor(Math.random()*r.length)];
}
function navigateTo(page){
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.sidebar nav a').forEach(a=>a.classList.remove('active'));
  const el=document.getElementById('page-'+page);
  if(el){el.classList.add('active');el.style.animation='none';el.offsetHeight;el.style.animation='';}
  const lnk=document.querySelector('.sidebar nav a[data-page="'+page+'"]');
  if(lnk)lnk.classList.add('active');
  closeSidebar();
  const R={dashboard:renderDashboard,mood:renderMoodLog,journal:renderJournal,trends:renderTrends,chat:renderChat,crisis:renderCrisis,profile:renderProfile,settings:renderSettings};
  if(R[page])R[page]();
}
function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.overlay').classList.toggle('open');}
function closeSidebar(){document.querySelector('.sidebar').classList.remove('open');document.querySelector('.overlay').classList.remove('open');}

// ================= NOTIFICATIONS =================

let _notifOpen = false;

async function loadNotifications() {
  try {
    const res  = await fetch('get_notifications.php');
    const data = await res.json();
    if (data.error) return;

    // Update badge
    const badge  = document.getElementById('notif-badge');
    const badgeM = document.getElementById('notif-badge-mobile');
    if (badge) {
      if (data.unread > 0) { badge.style.display='flex'; badge.textContent=data.unread>9?'9+':data.unread; }
      else badge.style.display='none';
    }
    if (badgeM) {
      if (data.unread > 0) { badgeM.style.display='flex'; badgeM.textContent=data.unread>9?'9+':data.unread; }
      else badgeM.style.display='none';
    }

    // Render list
    const list = document.getElementById('notif-list');
    if (!list) return;
    if (data.notifications.length === 0) {
      list.innerHTML = '<div class="notif-empty">🔔 No notifications yet</div>';
      return;
    }

    const icons = { reminder:'⏰', tip:'💡', alert:'⚠️', mood:'😊' };
    list.innerHTML = data.notifications.map(n => {
      const dt   = new Date(n.created_at);
      const time = isNaN(dt) ? '' : dt.toLocaleDateString('en',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});
      return `<div class="notif-item ${n.is_read==='f'||n.is_read===false?'unread':''}" onclick="window._readNotif('${n.id}',this)">
        <div class="notif-icon">${icons[n.type]||'🔔'}</div>
        <div class="notif-content">
          <div class="notif-title">${n.title}</div>
          <div class="notif-msg">${n.message}</div>
          <div class="notif-time">${time}</div>
        </div>
      </div>`;
    }).join('');
  } catch(e) { console.error('loadNotifications:', e); }
}

window._toggleNotifications = function() {
  const dropdown = document.getElementById('notif-dropdown');
  if (!dropdown) return;
  _notifOpen = !_notifOpen;
  dropdown.style.display = _notifOpen ? 'block' : 'none';
  if (_notifOpen) loadNotifications();
};

window._readNotif = async function(id, el) {
  el.classList.remove('unread');
  await fetch('mark_notifications_read.php', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ id })
  });
  loadNotifications();
};

window._markAllRead = async function() {
  await fetch('mark_notifications_read.php', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({})
  });
  loadNotifications();
  const list = document.getElementById('notif-list');
  if (list) list.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
};

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('notif-wrap');
  if (wrap && !wrap.contains(e.target) && _notifOpen) {
    _notifOpen = false;
    const dropdown = document.getElementById('notif-dropdown');
    if (dropdown) dropdown.style.display = 'none';
  }
});

// Poll for new notifications every 2 minutes
setInterval(loadNotifications, 120000);

// ================= CUSTOM CONFIRM DIALOG =================

function showConfirm({ icon = '❓', title, message, okText = 'Confirm', okColor = '#ef4444', onOk }) {
  const modal    = document.getElementById('confirm-modal');
  const overlay  = document.getElementById('confirm-overlay');
  const iconEl   = document.getElementById('confirm-icon');
  const titleEl  = document.getElementById('confirm-title');
  const msgEl    = document.getElementById('confirm-msg');
  const okBtn    = document.getElementById('confirm-ok');
  const cancelBtn= document.getElementById('confirm-cancel');

  iconEl.textContent   = icon;
  titleEl.textContent  = title;
  msgEl.textContent    = message;
  okBtn.textContent    = okText;
  okBtn.style.background = okColor;
  okBtn.style.color      = '#ffffff';

  modal.style.display = 'flex';

  const close = () => { modal.style.display = 'none'; };

  okBtn.onclick = () => { close(); if (onOk) onOk(); };
  cancelBtn.onclick = close;
  overlay.onclick   = close;
}

window._confirmLogout = function() {
  showConfirm({
    icon: '👋',
    title: 'Log out of MindBloom?',
    message: 'You\'ll need to sign in again to access your wellness journal.',
    okText: 'Log Out',
    okColor: '#ef4444',
    onOk: () => { window.location.href = 'logout.php'; }
  });
};
