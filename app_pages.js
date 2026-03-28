function renderMoodLog(){
  let selected=null;const container=document.getElementById('page-mood');
  function render(){
    const cfg=selected?MOOD_CONFIG[selected]:null;
    let html='<h1 style="font-size:24px;margin-bottom:4px;">How are you feeling?</h1><p style="color:var(--fg-muted);font-size:14px;margin-bottom:8px;">Select the mood that best describes you right now</p><div class="mood-grid">';
    for(const[k,v]of Object.entries(MOOD_CONFIG))html+='<div class="mood-btn '+(selected===k?'selected '+v.gradient:'')+'" onclick="window._selectMood(\''+k+'\')"><span class="emoji">'+v.emoji+'</span><span class="name">'+v.label+'</span></div>';
    html+='</div>';
    if(cfg)html+='<div class="card '+cfg.gradient+' mb-4"><div class="psychology-box" style="background:transparent;padding:0;"><div class="label">Color Psychology &mdash; '+cfg.label+'</div><p>'+cfg.psychology+'</p><div style="margin-top:12px;padding:12px;background:rgba(255,255,255,0.15);border-radius:8px;font-size:13px;">The interface now shows <strong>'+(THERAPEUTIC_DESCRIPTIONS[selected]||'supportive colors')+'</strong> to support your wellbeing.</div></div></div>';
    html+='<div class="mb-4"><label>Add a note (optional)</label><textarea id="mood-note" class="textarea" placeholder="What\'s on your mind?"></textarea></div><button class="btn btn-primary btn-block" '+(selected?'':'disabled')+' onclick="window._saveMood()">Save Mood</button>';
    container.innerHTML=html;if(selected)applyGlobalColorTheme(selected);
  }
  window._selectMood=function(m){selected=m;render();};
  window._saveMood=function(){
    if(!selected)return;const note=document.getElementById('mood-note')?.value||'';
    fetch('save_mood.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({mood:selected,intensity:5,triggers:'',activities:'',notes:note,timestamp:new Date().toISOString()})})
    .then(r=>r.json()).then(data=>{if(data.success){applyGlobalColorTheme(selected);window._latestMood=selected;container.innerHTML='<div class="saved-indicator" style="padding:40px;text-align:center;">Mood saved! '+MOOD_CONFIG[selected].emoji+'</div>';setTimeout(()=>{selected=null;render();},2000);}else{container.innerHTML='<div style="padding:40px;color:red;">Error: '+data.error+'</div>';setTimeout(()=>render(),3000);}})
    .catch(()=>{container.innerHTML='<div style="padding:40px;color:red;">Failed to save mood</div>';setTimeout(()=>render(),3000);});
  };
  render();
}
let _journalShowForm=false;window._journalMood=undefined;
window._toggleJournalForm=function(){_journalShowForm=!_journalShowForm;if(!_journalShowForm)window._journalMood=undefined;renderJournal();};
window._toggleJournalMood=function(el,mood){document.querySelectorAll('#j-moods .mood-tag-btn').forEach(b=>b.classList.remove('selected'));window._journalMood=(window._journalMood===mood)?undefined:mood;if(window._journalMood)el.classList.add('selected');};
window._saveJournal=async function(){
  const title=(document.getElementById('j-title')?.value||'').trim(),content=(document.getElementById('j-content')?.value||'').trim();
  const errBox=document.getElementById('j-error-box'),btn=document.getElementById('j-save-btn');
  if(errBox){errBox.style.display='none';errBox.textContent='';}
  if(!title){if(errBox){errBox.style.display='block';errBox.textContent='Please add a title.';}return;}
  if(!content){if(errBox){errBox.style.display='block';errBox.textContent='Please write something.';}return;}
  if(btn){btn.disabled=true;btn.textContent='Saving...';}
  try{const res=await fetch('save_journal.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title,content,mood:window._journalMood||null,timestamp:new Date().toISOString()})});const raw=await res.text();let data;try{data=JSON.parse(raw);}catch(e){if(btn){btn.disabled=false;btn.textContent='Save Entry';}if(errBox){errBox.style.display='block';errBox.textContent='Server error: '+raw.substring(0,200);}return;}if(data.success){_journalShowForm=false;window._journalMood=undefined;renderJournal();}else{if(btn){btn.disabled=false;btn.textContent='Save Entry';}if(errBox){errBox.style.display='block';errBox.textContent='Error: '+(data.error||JSON.stringify(data));}}}catch(e){if(btn){btn.disabled=false;btn.textContent='Save Entry';}if(errBox){errBox.style.display='block';errBox.textContent='Network error: '+e.message;}}
};
window._deleteJournal=async function(id){
  showConfirm({
    icon:'🗑️', title:'Delete Entry?',
    message:'This journal entry will be permanently deleted. This cannot be undone.',
    okText:'Delete', okColor:'#ef4444',
    onOk: async () => {
      try{const res=await fetch('delete_journal.php?id='+encodeURIComponent(id),{method:'DELETE'});const data=await res.json();if(data.success)renderJournal();else alert('Delete failed: '+(data.error||'Unknown'));}catch(e){alert('Network error: '+e.message);}
    }
  });
};;
async function renderJournal(){
  const container=document.getElementById('page-journal');if(!container)return;
  if(container.innerHTML.trim()===''){_journalShowForm=false;window._journalMood=undefined;}
  let formHtml='';
  if(_journalShowForm)formHtml='<div class="card mb-6"><div class="mb-4"><label>Title</label><input id="j-title" class="input" placeholder="Give your entry a title..." /></div><div class="mb-4"><label>What\'s on your mind?</label><textarea id="j-content" class="textarea" style="min-height:180px;" placeholder="Write freely..."></textarea></div><div class="mb-4"><p style="font-size:13px;font-weight:600;color:var(--fg);margin-bottom:10px;">Tag a mood (optional)</p><div style="display:flex;flex-wrap:wrap;gap:8px;" id="j-moods">'+Object.entries(MOOD_CONFIG).map(([k,v])=>'<button class="mood-tag-btn '+(window._journalMood===k?'selected':'')+'" data-mood="'+k+'" onclick="window._toggleJournalMood(this,\''+k+'\')">'+v.emoji+' '+v.label+'</button>').join('')+'</div></div><button class="btn btn-primary" id="j-save-btn" onclick="window._saveJournal()">Save Entry</button><div id="j-error-box" style="display:none;margin-top:12px;padding:12px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;font-size:13px;color:#991b1b;"></div></div>';
  let entries=[],fetchError='';
  try{const res=await fetch('get_journal.php');const text=await res.text();try{const p=JSON.parse(text);if(Array.isArray(p))entries=p;else fetchError=p.error||text.substring(0,150);}catch(e){fetchError=text.substring(0,150);}}catch(e){fetchError=e.message;}
  let listHtml='';
  if(fetchError)listHtml='<div style="padding:16px;background:#fee2e2;border-radius:8px;color:#991b1b;font-size:13px;">Could not load journal: '+esc(fetchError)+'</div>';
  else if(entries.length===0)listHtml='<div class="empty-state"><p style="font-size:32px;margin-bottom:12px;">&#128214;</p><p style="font-weight:600;margin-bottom:4px;">No entries yet</p><p>Click "+ New Entry" to start writing</p></div>';
  else listHtml=entries.map(e=>{const mc=e.mood&&MOOD_CONFIG[e.mood]?MOOD_CONFIG[e.mood]:null;const dt=new Date(e.timestamp);const dtStr=isNaN(dt.getTime())?'':dt.toLocaleDateString('en',{weekday:'long',year:'numeric',month:'long',day:'numeric'})+' &middot; '+dt.toLocaleTimeString('en',{hour:'2-digit',minute:'2-digit'});return '<div class="card journal-entry mb-4 '+(mc?mc.gradient:'')+'"><div style="display:flex;justify-content:space-between;align-items:start;gap:12px;"><div style="flex:1;min-width:0;"><h3 style="font-size:16px;margin-bottom:6px;">'+esc(e.title)+' '+(mc?mc.emoji:'')+'</h3><p style="font-size:14px;color:var(--fg-muted);margin-bottom:8px;white-space:pre-wrap;line-height:1.6;">'+esc(e.content)+'</p><p style="font-size:12px;color:var(--fg-muted);">'+dtStr+'</p></div><button class="delete-btn" onclick="window._deleteJournal(\''+e.id+'\')" title="Delete">&#128465;</button></div></div>';}).join('');
  container.innerHTML='<div class="journal-header mb-6"><div><h1 style="font-size:24px;">Journal</h1><p style="color:var(--fg-muted);font-size:14px;">Express your thoughts and feelings</p></div><button class="btn '+(_journalShowForm?'btn-outline':'btn-primary')+'" onclick="window._toggleJournalForm()">'+(_journalShowForm?'&#10005; Cancel':'+ New Entry')+'</button></div>'+formHtml+listHtml;
}
