<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
:root{
  --bg:#050712;
  --card:#161a33;
  --card2:#1f2649;
  --brand:#ff7b00;
  --brand2:#ffcc00;
  --ok:#20e3b2;
  --danger:#ff4d4d;
  --ink:#f5f7ff;
  --muted:rgba(255,255,255,.7);
}

body{background:var(--bg);}

.battle-topbar{
  background:linear-gradient(90deg,var(--brand),var(--brand2));
  padding:18px 14px;
  border-radius:22px;
  box-shadow:0 10px 24px rgba(0,0,0,.45);
  color:#fff;
  margin:12px auto 14px;
  max-width:1100px;
}

.battle-topbar h1{
  font-weight:900;
  margin:0;
  font-size:1.4rem;
}
.battle-topbar .sub{
  opacity:.95;
  font-weight:700;
  margin-top:4px;
}

.wrap{
  max-width:1100px;
  margin:0 auto 80px;
  padding:0 14px 20px;
}

.grid-3{
  display:grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap:14px;
}
@media(max-width: 992px){
  .grid-3{grid-template-columns:1fr; }
}

.panel{
  background:var(--card);
  border-radius:22px;
  box-shadow:0 10px 24px rgba(0,0,0,.55);
  padding:14px;
  color:var(--ink);
}

.panel-title{
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:10px;
}
.panel-title h3{
  margin:0;
  font-size:1.05rem;
  font-weight:900;
}
.badge-pill{
  background:rgba(255,255,255,.15);
  padding:6px 10px;
  border-radius:999px;
  font-weight:800;
  font-size:.85rem;
}

.card-item{
  background:var(--card2);
  border-radius:18px;
  padding:12px;
  margin-bottom:10px;
  border:2px solid transparent;
  box-shadow:0 8px 18px rgba(0,0,0,.35);
  transition:transform .12s ease, border-color .12s ease;
}
.card-item:hover{
  transform:translateY(-2px);
  border-color:rgba(255,204,0,.55);
}

.row-flex{
  display:flex; align-items:center; justify-content:space-between;
  gap:12px;
}

.user-chip{
  display:flex; align-items:center; gap:10px;
}
.avatar{
  width:42px; height:42px; border-radius:50%;
  background:#2b3361;
  display:flex; align-items:center; justify-content:center;
  font-weight:900;
  color:#ffd700;
  overflow:hidden;
}
.avatar img{width:100%; height:100%; object-fit:cover;}

.user-name{font-weight:900;}
.user-meta{font-size:.85rem; color:var(--muted); margin-top:2px;}

.btnx{
  border:0;
  border-radius:14px;
  padding:8px 12px;
  font-weight:900;
  cursor:pointer;
  transition:transform .12s ease, opacity .12s ease;
  display:inline-flex;
  align-items:center;
  gap:8px;
  user-select:none;
  white-space:nowrap;
}
.btnx:active{transform:scale(.98);}
.btn-green{background:var(--ok); color:#003326;}
.btn-orange{background:linear-gradient(90deg,var(--brand),var(--brand2)); color:#000;}
.btn-red{background:var(--danger); color:#fff;}
.btn-dark{background:rgba(255,255,255,.12); color:#fff;}

.small{font-size:.85rem;}
.muted{color:var(--muted);}

.kpi{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:10px;
  margin-top:12px;
}
.kpi .box{
  background:rgba(255,255,255,.12);
  border-radius:18px;
  padding:10px 12px;
  text-align:center;
}
.kpi .n{font-weight:900; font-size:1.1rem;}
.kpi .t{font-weight:800; font-size:.85rem; opacity:.9;}

.hr{
  height:1px; background:rgba(255,255,255,.12);
  margin:10px 0;
}

.toastx{
  position:fixed;
  left:50%;
  transform:translateX(-50%);
  bottom:18px;
  background:#11162f;
  color:#fff;
  padding:10px 14px;
  border-radius:14px;
  box-shadow:0 14px 30px rgba(0,0,0,.6);
  display:none;
  z-index:9999;
  border:1px solid rgba(255,255,255,.12);
}
.toastx.show{display:block;}
</style>

<?php
  // You can pass $quizId from controller or set it in HTML via data attribute
  $quizId = (int)($quizId ?? 0);
?>

<div class="battle-topbar">
  <h1>?? Quiz Battles (1v1)</h1>
  <div class="sub">Challenge a student • Play async • Winner = More correct + Faster ??</div>

  <div class="kpi">
    <div class="box">
      <div class="n" id="kpiInvites">0</div>
      <div class="t">Invites</div>
    </div>
    <div class="box">
      <div class="n" id="kpiActive">0</div>
      <div class="t">Active Battles</div>
    </div>
    <div class="box">
      <div class="n" id="kpiWins">0</div>
      <div class="t">Wins</div>
    </div>
  </div>
</div>

<div class="wrap">
  <div class="grid-3">

    <!-- LEFT: Create Battle -->
    <div class="panel">
      <div class="panel-title">
        <h3>?? Create Challenge</h3>
        <span class="badge-pill">Async Mode</span>
      </div>

      <div class="card-item">
        <div class="muted small">Selected Quiz</div>
        <div style="font-weight:900; margin-top:4px;">
          Quiz ID: <span id="quizIdTxt"><?= $quizId ?: '-' ?></span>
        </div>
        <div class="small muted" style="margin-top:6px;">
          Tip: Use the same quiz for fair battle.
        </div>
      </div>

      <div class="hr"></div>

      <div class="muted small mb-2">Choose opponent</div>
      <div id="opponentsBox">
        <div class="muted small">Loading opponents…</div>
      </div>

      <div class="hr"></div>

      <button class="btnx btn-dark w-100" id="btnRefresh">
        ?? Refresh
      </button>
    </div>

    <!-- MID: Pending Invites -->
    <div class="panel">
      <div class="panel-title">
        <h3>?? Pending Invites</h3>
        <span class="badge-pill" id="invitesCount">0</span>
      </div>

      <div id="invitesBox">
        <div class="muted small">Loading invites…</div>
      </div>
    </div>

    <!-- RIGHT: My Battles -->
    <div class="panel">
      <div class="panel-title">
        <h3>??? My Battles</h3>
        <span class="badge-pill" id="battlesCount">0</span>
      </div>

      <div id="battlesBox">
        <div class="muted small">Loading battles…</div>
      </div>
    </div>

  </div>
</div>

<div class="toastx" id="toastx"></div>

<script>
(function(){
  const QUIZ_ID = parseInt("<?= (int)$quizId ?>", 10) || 0;

  // ---- endpoints (change if your routes differ)
  const URL_DATA    = "<?= base_url('frontend/battles/data') ?>";
  const URL_CREATE  = "<?= base_url('frontend/battles/create') ?>";
  const URL_ACCEPT  = "<?= base_url('frontend/battles/accept') ?>";
  const URL_DECLINE = "<?= base_url('frontend/battles/decline') ?>";

  const opponentsBox = document.getElementById('opponentsBox');
  const invitesBox   = document.getElementById('invitesBox');
  const battlesBox   = document.getElementById('battlesBox');

  const invitesCount = document.getElementById('invitesCount');
  const battlesCount = document.getElementById('battlesCount');

  const kpiInvites = document.getElementById('kpiInvites');
  const kpiActive  = document.getElementById('kpiActive');
  const kpiWins    = document.getElementById('kpiWins');

  const btnRefresh = document.getElementById('btnRefresh');

  function toast(msg){
    const t = document.getElementById('toastx');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'), 2600);
  }

  async function post(url, data){
    const fd = new FormData();
    Object.keys(data || {}).forEach(k => fd.append(k, data[k]));
    const res = await fetch(url, { method:'POST', body: fd, headers: { 'X-Requested-With':'XMLHttpRequest' } });
    return await res.json();
  }

  async function get(url){
    const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
    return await res.json();
  }

  function initials(name){
    name = (name || '').trim();
    if(!name) return 'U';
    const parts = name.split(/\s+/);
    const a = (parts[0] || 'U')[0];
    const b = (parts[1] || '')[0] || '';
    return (a + b).toUpperCase();
  }

  function avatarHtml(photoUrl, name){
    if(photoUrl){
      return `<div class="avatar"><img src="${photoUrl}" alt=""></div>`;
    }
    return `<div class="avatar">${initials(name)}</div>`;
  }

  function renderOpponents(list){
    if(!Array.isArray(list) || !list.length){
      opponentsBox.innerHTML = `<div class="muted small">No opponents found.</div>`;
      return;
    }

    opponentsBox.innerHTML = list.map(u => `
      <div class="card-item">
        <div class="row-flex">
          <div class="user-chip">
            ${avatarHtml(u.photo_url, u.name)}
            <div>
              <div class="user-name">${escapeHtml(u.name)}</div>
              <div class="user-meta">${escapeHtml(u.meta || 'Student')}</div>
            </div>
          </div>
          <button class="btnx btn-orange small" data-action="challenge" data-id="${u.student_id}">
            ?? Challenge
          </button>
        </div>
      </div>
    `).join('');

    opponentsBox.querySelectorAll('[data-action="challenge"]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const opponentId = parseInt(btn.getAttribute('data-id'), 10) || 0;
        if(!QUIZ_ID){
          toast('Quiz ID missing. Open battle lobby from a quiz.');
          return;
        }
        btn.disabled = true;
        const r = await post(URL_CREATE, { quiz_id: QUIZ_ID, opponent_id: opponentId });
        btn.disabled = false;
        if(r && r.success){
          toast('Challenge sent! ??');
          await loadAll();
        }else{
          toast((r && r.message) ? r.message : 'Failed to create battle');
        }
      });
    });
  }

  function renderInvites(list){
    if(!Array.isArray(list) || !list.length){
      invitesBox.innerHTML = `<div class="muted small">No pending invites.</div>`;
      invitesCount.textContent = '0';
      return;
    }
    invitesCount.textContent = String(list.length);
    kpiInvites.textContent = String(list.length);

    invitesBox.innerHTML = list.map(b => `
      <div class="card-item">
        <div class="row-flex">
          <div class="user-chip">
            ${avatarHtml(b.from_photo_url, b.from_name)}
            <div>
              <div class="user-name">?? ${escapeHtml(b.from_name)}</div>
              <div class="user-meta">
                Quiz: <b>#${b.quiz_id}</b> • ${escapeHtml(b.quiz_title || '')}
              </div>
            </div>
          </div>
        </div>

        <div class="row-flex" style="margin-top:10px;">
          <button class="btnx btn-green small" data-action="accept" data-id="${b.battle_id}">
            ? Accept
          </button>
          <button class="btnx btn-red small" data-action="decline" data-id="${b.battle_id}">
            ? Decline
          </button>
        </div>
      </div>
    `).join('');

    invitesBox.querySelectorAll('[data-action="accept"]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = parseInt(btn.getAttribute('data-id'),10) || 0;
        btn.disabled = true;
        const r = await post(URL_ACCEPT, { battle_id: id });
        btn.disabled = false;
        if(r && r.success){
          toast('Battle accepted! ???');
          // if backend returns play_url, go there
          if(r.play_url){ window.location.href = r.play_url; return; }
          await loadAll();
        }else toast((r && r.message) ? r.message : 'Failed');
      });
    });

    invitesBox.querySelectorAll('[data-action="decline"]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = parseInt(btn.getAttribute('data-id'),10) || 0;
        btn.disabled = true;
        const r = await post(URL_DECLINE, { battle_id: id });
        btn.disabled = false;
        if(r && r.success){
          toast('Invite declined.');
          await loadAll();
        }else toast((r && r.message) ? r.message : 'Failed');
      });
    });
  }

  function renderBattles(list, stats){
    const activeCount = (stats && stats.active) ? stats.active : 0;
    const winsCount   = (stats && stats.wins) ? stats.wins : 0;

    kpiActive.textContent = String(activeCount);
    kpiWins.textContent   = String(winsCount);

    if(!Array.isArray(list) || !list.length){
      battlesBox.innerHTML = `<div class="muted small">No battles yet.</div>`;
      battlesCount.textContent = '0';
      return;
    }

    battlesCount.textContent = String(list.length);

    battlesBox.innerHTML = list.map(b => {
      const st = (b.status || '').toLowerCase();
      const statusLabel =
        st === 'pending'   ? '? Pending' :
        st === 'active'    ? '??? Active' :
        st === 'completed' ? '?? Completed' :
        '—';

      const canPlay = (st === 'active' || st === 'pending'); // backend decides in play url
      const btn = canPlay
        ? `<a class="btnx btn-orange small" href="${escapeAttr(b.play_url || '#')}">? Play</a>`
        : `<a class="btnx btn-dark small" href="${escapeAttr(b.view_url || '#')}">?? View</a>`;

      return `
        <div class="card-item">
          <div class="row-flex">
            <div class="user-chip">
              ${avatarHtml(b.opponent_photo_url, b.opponent_name)}
              <div>
                <div class="user-name">${escapeHtml(b.opponent_name || 'Opponent')}</div>
                <div class="user-meta">
                  ${statusLabel} • Quiz <b>#${b.quiz_id}</b>
                </div>
              </div>
            </div>
            ${btn}
          </div>
          <div class="muted small" style="margin-top:8px;">
            ${escapeHtml(b.quiz_title || '')}
            ${b.result_text ? ' • ' + escapeHtml(b.result_text) : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  function escapeHtml(str){
    return String(str ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }
  function escapeAttr(str){ return escapeHtml(str); }

  async function loadAll(){
    try{
      const data = await get(URL_DATA);

      // expected JSON structure:
      // {
      //   "success": true,
      //   "opponents": [{student_id,name,photo_url,meta}],
      //   "invites": [{battle_id,quiz_id,quiz_title,from_name,from_photo_url}],
      //   "battles": [{battle_id,quiz_id,quiz_title,opponent_name,opponent_photo_url,status,play_url,view_url,result_text}],
      //   "stats": { "active": 2, "wins": 5 }
      // }

      if(!data || data.success === false){
        toast(data && data.message ? data.message : 'Failed to load');
        return;
      }

      renderOpponents(data.opponents || []);
      renderInvites(data.invites || []);
      renderBattles(data.battles || [], data.stats || {});

      // KPI invites (if backend already returned count)
      if (Array.isArray(data.invites)) kpiInvites.textContent = String(data.invites.length);

    }catch(e){
      toast('Network error while loading battles.');
    }
  }

  btnRefresh.addEventListener('click', loadAll);

  // initial load + auto refresh
  loadAll();
  setInterval(loadAll, 15000);
})();
</script>

<?= $this->endSection() ?>
