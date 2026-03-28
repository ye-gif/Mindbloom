async function renderTrends(){
  const container=document.getElementById('page-trends');
  container.innerHTML='<p style="color:var(--fg-muted);padding:40px;text-align:center;">Loading...</p>';
  const entries=await fetchMoods();
  if(entries.length===0){container.innerHTML='<h1 style="font-size:24px;margin-bottom:4px;">Mood Trends</h1><p style="color:var(--fg-muted);font-size:14px;margin-bottom:24px;">Discover patterns in your emotional wellbeing</p><div class="empty-state"><p>No mood data yet</p><p>Log your moods to see trends here</p></div>';return;}
  function ldk(d){return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');}
  const byDate={};entries.forEach(e=>{const k=ldk(new Date(e.timestamp));if(!byDate[k])byDate[k]=[];byDate[k].push(e);});
  const last7=[];for(let i=6;i>=0;i--){const d=new Date();d.setDate(d.getDate()-i);last7.push(d);}
  const wData=last7.map(d=>{const k=ldk(d),de=byDate[k]||[],mc={};de.forEach(e=>{mc[e.mood]=(mc[e.mood]||0)+1;});const dom=Object.entries(mc).sort((a,b)=>b[1]-a[1])[0]?.[0]||null;return{label:d.toLocaleDateString('en',{weekday:'short'}),key:k,count:de.length,dominant:dom};});
  const maxC=Math.max(...wData.map(d=>d.count),1),tc={};entries.forEach(e=>{tc[e.mood]=(tc[e.mood]||0)+1;});
  const total=entries.length,todayKey=ldk(new Date()),todayE=byDate[todayKey]||[];
  let html='<h1 style="font-size:24px;margin-bottom:4px;">Mood Trends</h1><p style="color:var(--fg-muted);font-size:14px;margin-bottom:24px;">Your emotional patterns</p>';
  html+='<div class="card mb-6"><h2 style="font-size:16px;margin-bottom:4px;">Today\'s Mood Breakdown</h2><p style="font-size:12px;color:var(--fg-muted);margin-bottom:20px;">'+todayE.length+' mood'+(todayE.length!==1?'s':'')+' logged today</p>';
  if(todayE.length===0)html+='<p style="color:var(--fg-muted);font-size:14px;text-align:center;padding:20px 0;">No moods logged today yet</p>';
  else{html+='<div class="trends-today-chart">';todayE.slice().reverse().forEach(e=>{const c=MOOD_CONFIG[e.mood]||MOOD_CONFIG.neutral;const dt=new Date(e.timestamp);const time=isNaN(dt)?'':dt.toLocaleTimeString('en',{hour:'2-digit',minute:'2-digit'});html+='<div class="trends-today-item" style="border-left:4px solid '+c.color+';"><span class="trends-today-emoji">'+c.emoji+'</span><span class="trends-today-label">'+c.label+'</span><span class="trends-today-time">'+time+'</span></div>';});html+='</div>';}
  html+='</div><div class="card-grid card-grid-2 mb-6"><div class="card"><h2 style="font-size:16px;margin-bottom:4px;">This Week</h2><p style="font-size:12px;color:var(--fg-muted);margin-bottom:20px;">Moods logged per day</p><div class="trends-bar-chart">';
  wData.forEach(d=>{const pct=maxC>0?Math.round((d.count/maxC)*100):0;const color=d.dominant?MOOD_CONFIG[d.dominant].color:'#e2e8f0';const emoji=d.dominant?MOOD_CONFIG[d.dominant].emoji:'&middot;';const isT=d.key===todayKey;html+='<div class="trends-bar-col'+(isT?' trends-bar-today':'')+'"><div class="trends-bar-value">'+(d.count>0?d.count:'')+'</div><div class="trends-bar-wrap"><div class="trends-bar" style="height:'+Math.max(pct,d.count>0?8:0)+'%;background:'+color+';"></div></div><div class="trends-bar-emoji">'+emoji+'</div><div class="trends-bar-label">'+d.label+'</div>'+(isT?'<div class="trends-bar-today-dot"></div>':'')+'</div>';});
  html+='</div></div><div class="card"><h2 style="font-size:16px;margin-bottom:4px;">All-Time Distribution</h2><p style="font-size:12px;color:var(--fg-muted);margin-bottom:16px;">'+total+' total entries</p><div class="trends-distribution">';
  Object.entries(tc).sort((a,b)=>b[1]-a[1]).forEach(([mood,count])=>{const c=MOOD_CONFIG[mood]||MOOD_CONFIG.neutral;const pct=Math.round((count/total)*100);html+='<div class="trends-dist-row"><div class="trends-dist-label">'+c.emoji+' '+c.label+'</div><div class="trends-dist-bar-wrap"><div class="trends-dist-bar" style="width:'+pct+'%;background:'+c.color+';"></div></div><div class="trends-dist-count">'+count+' <span style="color:var(--fg-muted);font-weight:400;">('+pct+'%)</span></div></div>';});
  html+='</div></div></div><div class="card"><h2 style="font-size:16px;margin-bottom:16px;">Recent Mood History</h2><div class="trends-history">';
  entries.slice(0,20).forEach(e=>{const c=MOOD_CONFIG[e.mood]||MOOD_CONFIG.neutral;const dt=new Date(e.timestamp);const label=isNaN(dt)?'&mdash;':dt.toLocaleDateString('en',{weekday:'short',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});html+='<div class="trends-history-item" style="border-left:4px solid '+c.color+';"><div class="trends-history-emoji">'+c.emoji+'</div><div class="trends-history-info"><div class="trends-history-name">'+c.label+'</div>'+(e.notes?'<div class="trends-history-note">'+esc(e.notes)+'</div>':'')+'</div><div class="trends-history-time">'+label+'</div></div>';});
  html+='</div></div>';container.innerHTML=html;
}
function renderChat(){
  const container=document.getElementById('page-chat');
  let messages=JSON.parse(localStorage.getItem('mindbloom-chat')||'[]');
  if(messages.length===0){messages=[{id:uuid(),role:'bot',content:"Hi there! I'm MindBloom AI, your mental wellness companion. How are you feeling today?",timestamp:new Date().toISOString()}];localStorage.setItem('mindbloom-chat',JSON.stringify(messages));}
  function render(isTyping){
    let html='<div class="page-header"><h1>AI Wellness Chat</h1><p>A safe space to express your feelings &mdash; powered by Gemini AI</p></div><div class="chat-container"><div class="chat-messages" id="chat-msgs">';
    messages.forEach(m=>{const content=m.role==='bot'?m.content.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>'):esc(m.content);html+='<div class="chat-msg '+m.role+'"><div class="chat-avatar">'+(m.role==='bot'?'&#127807;':'&#128100;')+'</div><div class="chat-bubble">'+content+'</div></div>';});
    if(isTyping)html+='<div class="chat-msg bot"><div class="chat-avatar">&#127807;</div><div class="chat-bubble chat-typing"><span></span><span></span><span></span></div></div>';
    html+='</div><div class="chat-input-row"><input id="chat-input" class="input" placeholder="Share what\'s on your mind..." onkeydown="if(event.key===\'Enter\'&&!event.shiftKey){event.preventDefault();window._sendChat();}"/><button class="chat-send" id="chat-send-btn" onclick="window._sendChat()">&#10148;</button></div></div>';
    container.innerHTML=html;const msgs=document.getElementById('chat-msgs');if(msgs)msgs.scrollTop=msgs.scrollHeight;
  }
  window._sendChat=async function(){
    const input=document.getElementById('chat-input'),sendBtn=document.getElementById('chat-send-btn');
    const text=input?.value?.trim();if(!text)return;
    messages.push({id:uuid(),role:'user',content:text,timestamp:new Date().toISOString()});
    input.value='';if(sendBtn)sendBtn.disabled=true;render(true);
    try{const res=await fetch('chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text,recent_mood:window._latestMood||''})});const data=await res.json();messages.push({id:uuid(),role:'bot',content:data.reply||('Error: '+(data.error||'Unknown')),timestamp:new Date().toISOString()});}
    catch(e){messages.push({id:uuid(),role:'bot',content:'Could not connect to AI. Please try again.',timestamp:new Date().toISOString()});}
    localStorage.setItem('mindbloom-chat',JSON.stringify(messages));if(sendBtn)sendBtn.disabled=false;render(false);
  };
  render(false);
}
function renderCrisis(){document.getElementById('page-crisis').innerHTML='<div class="crisis-banner mb-6"><h1>Crisis Support</h1><p>If you or someone you know is in immediate danger, please call <strong>911</strong>.</p></div><div class="section-header"><h2>Emergency &amp; Support Hotlines</h2></div><div class="card-grid mb-6"><div class="card hotline-card"><div class="hotline-icon">&#128222;</div><div class="hotline-content"><h3>988 Suicide &amp; Crisis Lifeline</h3><p>Free, confidential support available 24/7</p><div class="hotline-links"><a href="tel:988">&#128222; Call 988</a><span>&#128172; Text HOME to 741741</span></div></div></div><div class="card hotline-card"><div class="hotline-icon">&#128172;</div><div class="hotline-content"><h3>Crisis Text Line</h3><p>Speak with a trained counselor via text</p><div class="hotline-links"><span>&#128172; Text HOME to 741741</span></div></div></div><div class="card hotline-card"><div class="hotline-icon">&#10084;&#65039;</div><div class="hotline-content"><h3>SAMHSA Helpline</h3><p>Mental health &amp; substance use support</p><div class="hotline-links"><a href="tel:1-800-662-4357">&#128222; Call 1-800-662-4357</a></div></div></div><div class="card hotline-card"><div class="hotline-icon">&#127757;</div><div class="hotline-content"><h3>International Help</h3><p>Find crisis centers worldwide</p><div class="hotline-links"><a href="https://www.iasp.info/resources/Crisis_Centres/" target="_blank">&#128279; Visit Website</a></div></div></div></div><div class="card text-center"><p style="font-size:14px;color:var(--fg-muted);">&#127807; You are not alone. Help is always available.</p></div>';}
