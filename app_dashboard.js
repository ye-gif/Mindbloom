async function renderDashboard(){
  const [moods,journals]=await Promise.all([fetchMoods(),fetchJournal()]);
  const latest=moods.length>0?moods[0].mood:null;
  const cfg=latest?MOOD_CONFIG[latest]:null;
  const tips=SELF_CARE_TIPS[latest||'neutral'];
  const today=moods.filter(e=>new Date(e.timestamp).toDateString()===new Date().toDateString());
  const quote=getRandomQuote(latest||'neutral');
  const tipsHtml=tips.slice(0,4).map((t,i)=>{
    if(t.type==='breathing')return '<div class="tip-item tip-breathing" onclick="window._openBreathing('+i+')"><span class="tip-breath-icon">&#x1FAC1;</span><div style="flex:1"><div style="font-size:13px;font-weight:600;color:var(--fg)">'+t.label+'</div><div style="font-size:12px;color:var(--fg-muted)">'+t.text+'</div></div><span class="tip-start-btn">Start</span></div>';
    return '<div class="tip-item"><span class="dot">&bull;</span>'+t.text+'</div>';
  }).join('');
  const recentHtml=moods.length>0?'<div class="card mb-6"><div class="section-header"><h2>Recent Moods</h2><a onclick="navigateTo(\'trends\')">View all</a></div><div class="mood-chips">'+moods.slice(0,7).map(e=>{const c=MOOD_CONFIG[e.mood]||MOOD_CONFIG.neutral;return '<div class="mood-chip '+c.gradient+'"><div class="emoji">'+c.emoji+'</div><div class="day">'+new Date(e.timestamp).toLocaleDateString('en',{weekday:'short'})+'</div></div>';}).join('')+'</div></div>':'';
  document.getElementById('page-dashboard').innerHTML=
    '<div class="hero '+(cfg?cfg.gradient:'mood-bg-neutral')+'"><h1>Welcome <span>'+USERNAME+'</span>!</h1>'+(cfg?'<p>You\'re feeling <span class="mood-tag">'+cfg.emoji+' '+cfg.label+'</span> today</p><div class="psychology-box"><div class="label">Color Psychology</div><p>'+cfg.psychology+'</p></div>':'<p>Start by logging your mood for today</p>')+'</div>'+
    '<div class="section-header"><h2>Quick Actions</h2></div>'+
    '<div class="card-grid card-grid-4 mb-6">'+
    '<div class="card quick-action" onclick="navigateTo(\'mood\')"><div class="quick-action-icon"><img src="assets/emoji.gif" alt="Log Mood"></div><div class="quick-action-content"><div class="title">Log Mood</div><div class="desc">How are you feeling?</div></div></div>'+
    '<div class="card quick-action" onclick="navigateTo(\'journal\')"><div class="quick-action-icon"><img src="assets/notes.gif" alt="Journal"></div><div class="quick-action-content"><div class="title">Write Journal</div><div class="desc">Express your thoughts</div></div></div>'+
    '<div class="card quick-action" onclick="navigateTo(\'trends\')"><div class="quick-action-icon"><img src="assets/trends.gif" alt="Trends"></div><div class="quick-action-content"><div class="title">View Trends</div><div class="desc">See your patterns</div></div></div>'+
    '<div class="card quick-action" onclick="navigateTo(\'crisis\')"><div class="quick-action-icon"><img src="assets/call.gif" alt="Crisis"></div><div class="quick-action-content"><div class="title">Crisis Help</div><div class="desc">Get immediate support</div></div></div>'+
    '</div>'+
    '<div class="card-grid card-grid-2 mb-6">'+
    '<div class="card"><h2 style="font-size:16px;margin-bottom:12px;">Today\'s Activity</h2><div class="stat-row"><span class="label">Moods logged today</span><span class="value">'+today.length+'</span></div><div class="stat-row"><span class="label">Total journal entries</span><span class="value">'+journals.length+'</span></div><div class="stat-row"><span class="label">Total moods tracked</span><span class="value">'+moods.length+'</span></div></div>'+
    '<div class="card"><h2 style="font-size:16px;margin-bottom:12px;">Self-Care Tips</h2>'+tipsHtml+'</div>'+
    '</div>'+
    '<div class="card quote-card mb-6"><div class="quote-icon">&#10077;</div><blockquote class="quote-text">'+quote.text+'</blockquote><div class="quote-author">&#8212; '+quote.author+'</div><button class="quote-refresh" onclick="window._refreshQuote(\''+( latest||'neutral')+'\')">&#8635; New Quote</button></div>'+
    recentHtml;
  // Only apply color theme if user has logged a mood before
  if (latest) {
    applyGlobalColorTheme(latest);
  } else {
    // New user — clear any theme, keep default white background
    const all = ['mood-happy-ui','mood-calm-ui','mood-sad-ui','mood-anxious-ui','mood-angry-ui','mood-neutral-ui'];
    document.body.classList.remove(...all);
    document.documentElement.style.removeProperty('--primary');
    document.documentElement.style.removeProperty('--primary-light');
  }
  window._currentTips=tips;
  window._refreshQuote=function(mood){const q=getRandomQuote(mood);const tEl=document.querySelector('.quote-text'),aEl=document.querySelector('.quote-author');if(tEl){tEl.style.opacity='0';setTimeout(()=>{tEl.textContent=q.text;tEl.style.opacity='1';},200);}if(aEl){aEl.style.opacity='0';setTimeout(()=>{aEl.textContent='\u2014 '+q.author;aEl.style.opacity='1';},200);}};
}
window._breathingTimer=null;
window._openBreathing=function(i){const tip=window._currentTips?.[i];if(!tip||tip.type!=='breathing')return;document.getElementById('breathing-modal')?.remove();const m=document.createElement('div');m.id='breathing-modal';m.innerHTML='<div class="breath-overlay" onclick="window._closeBreathing()"></div><div class="breath-modal"><button class="breath-close" onclick="window._closeBreathing()">&#10005;</button><h2 class="breath-title">'+tip.label+'</h2><p class="breath-subtitle">'+tip.text+'</p><div class="breath-circle-wrap"><div class="breath-circle" id="breath-circle"><div class="breath-phase" id="breath-phase">Ready</div><div class="breath-count" id="breath-count">&#8212;</div></div></div><div class="breath-progress-wrap"><div class="breath-progress-bar" id="breath-progress"></div></div><div class="breath-total" id="breath-total">30 seconds remaining</div><div style="display:flex;gap:10px;justify-content:center;margin-top:20px;"><button class="btn btn-primary" id="breath-start-btn" onclick="window._startBreathing('+JSON.stringify(tip.pattern)+')">Start Session</button><button class="btn btn-outline" onclick="window._closeBreathing()">Cancel</button></div></div>';document.body.appendChild(m);};
window._startBreathing=function(pattern){const TOTAL=30,names=['Inhale','Hold','Exhale','Hold'],colors=['#22c55e','#f59e0b','#3b82f6','#a855f7'];const active=pattern.map((d,i)=>({duration:d,name:names[i],color:colors[i]})).filter(p=>p.duration>0);const cEl=document.getElementById('breath-circle'),phEl=document.getElementById('breath-phase'),cnEl=document.getElementById('breath-count'),prEl=document.getElementById('breath-progress'),tEl=document.getElementById('breath-total'),sBtn=document.getElementById('breath-start-btn');if(sBtn)sBtn.style.display='none';let elapsed=0,pIdx=0,pElapsed=0;function tick(){if(elapsed>=TOTAL){if(cEl){cEl.style.borderColor='#22c55e';cEl.style.transform='scale(1)';}if(phEl)phEl.textContent='Done!';if(cnEl)cnEl.textContent='';if(tEl)tEl.textContent='Great job!';if(prEl)prEl.style.width='100%';if(sBtn){sBtn.style.display='inline-flex';sBtn.textContent='Start Again';}clearInterval(window._breathingTimer);return;}const p=active[pIdx%active.length],rem=p.duration-pElapsed;if(cEl)cEl.style.borderColor=p.color;if(phEl)phEl.textContent=p.name;if(cnEl)cnEl.textContent=rem;if(cEl){if(p.name==='Inhale')cEl.style.transform='scale('+(1+(pElapsed/p.duration)*0.25)+')';else if(p.name==='Exhale')cEl.style.transform='scale('+(1.25-(pElapsed/p.duration)*0.25)+')';else cEl.style.transform='scale(1.1)';}if(prEl)prEl.style.width=((elapsed/TOTAL)*100)+'%';if(tEl)tEl.textContent=(TOTAL-elapsed)+' seconds remaining';pElapsed++;elapsed++;if(pElapsed>=p.duration){pElapsed=0;pIdx++;}}clearInterval(window._breathingTimer);tick();window._breathingTimer=setInterval(tick,1000);};
window._closeBreathing=function(){clearInterval(window._breathingTimer);document.getElementById('breathing-modal')?.remove();};
