<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','ChordGen')</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<script>tailwind.config={theme:{extend:{fontFamily:{display:['Syne','sans-serif'],mono:['JetBrains Mono','monospace'],body:['Inter','sans-serif']}}}}</script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#04040a;color:#e2e8f0;min-height:100vh}
.grid-bg{background-image:linear-gradient(rgba(251,191,36,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(251,191,36,.025) 1px,transparent 1px);background-size:40px 40px}
::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-track{background:#0a0a14}::-webkit-scrollbar-thumb{background:#2a2a40;border-radius:3px}
.glow-amber{box-shadow:0 0 18px rgba(245,158,11,.22),0 0 36px rgba(245,158,11,.08)}
.text-glow{text-shadow:0 0 24px rgba(245,158,11,.45)}
.glass{background:rgba(12,12,24,.85);backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.055)}

/* Wave animation */
@keyframes wave{0%,100%{height:6px}50%{height:18px}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes slideUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
@keyframes itemIn{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}
.wave-bar{display:inline-block;width:3px;background:#f59e0b;border-radius:2px;animation:wave 1.1s ease-in-out infinite}
.wave-bar:nth-child(2){animation-delay:.1s}.wave-bar:nth-child(3){animation-delay:.2s}
.wave-bar:nth-child(4){animation-delay:.3s}.wave-bar:nth-child(5){animation-delay:.4s}
.animate-slide-up{animation:slideUp .45s ease-out both}
.chord-item{animation:itemIn .3s ease-out both}
.chord-item:nth-child(1){animation-delay:.03s}.chord-item:nth-child(2){animation-delay:.06s}
.chord-item:nth-child(3){animation-delay:.09s}.chord-item:nth-child(4){animation-delay:.12s}
.chord-item:nth-child(n+5){animation-delay:.15s}
.spin{animation:spin .7s linear infinite}


.piano-key{display:inline-flex;align-items:center;justify-content:center;border-radius:3px;font-size:9px;font-family:'JetBrains Mono',monospace;padding:2px 4px}
.piano-key.white{background:#dde;color:#111;border:1px solid #bbc}
.piano-key.black{background:#1a1a2e;color:#8888aa;border:1px solid #333355}

input[type=range]{-webkit-appearance:none;background:#181825;border-radius:3px;height:3px;width:100%}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:14px;height:14px;background:#f59e0b;border-radius:50%;cursor:pointer}
select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23ffffff35' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center}

.col-sidebar{
    display:flex;
    flex-direction:column;
    gap:1rem;
}


.form-sticky{
    position:sticky;
    top:72px;
    z-index:10;
}

.form-inner{
    overflow:visible;
    max-height:none;
}


.history-panel{
    position:static;
}


.player{background:rgba(8,8,18,.98);backdrop-filter:blur(22px);border-top:1px solid rgba(245,158,11,.18)}
.prog-track{height:4px;background:#1a1a2e;border-radius:2px;cursor:pointer;position:relative}
.prog-fill{height:100%;background:linear-gradient(90deg,#f59e0b,#fbbf24);border-radius:2px;transition:width .07s linear;pointer-events:none}
.active-card{border-color:rgba(245,158,11,.65)!important;box-shadow:0 0 12px rgba(245,158,11,.2)!important;background:rgba(245,158,11,.05)!important}


.hist-item:hover{background:rgba(255,255,255,.03)}
.hist-item.active{background:rgba(245,158,11,.055);border-left:2px solid #f59e0b}


#audio-loading{display:none;position:fixed;inset:0;background:rgba(4,4,10,.7);z-index:200;align-items:center;justify-content:center;flex-direction:column;gap:12px;backdrop-filter:blur(4px)}
#audio-loading.show{display:flex}


</style>
</head>
<body class="grid-bg">

<!-- HEADER -->
<header class="sticky top-0 z-50 border-b border-white/[.04]" style="background:rgba(4,4,10,.97);backdrop-filter:blur(22px)">
  <div class="max-w-[1400px] mx-auto px-4 sm:px-6 h-[64px] flex items-center justify-between">
    <a href="{{ route('chord.index') }}" class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-lg flex items-center justify-center glow-amber" style="background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.28)">
        <div class="flex items-end gap-0.5 h-[18px] px-0.5">
          <span class="wave-bar" style="height:6px"></span><span class="wave-bar" style="height:12px"></span>
          <span class="wave-bar" style="height:18px"></span><span class="wave-bar" style="height:12px"></span>
          <span class="wave-bar" style="height:6px"></span>
        </div>
      </div>
      <div>
        <div class="font-display font-bold text-[17px] text-white tracking-tight leading-none">Chord<span class="text-amber-400">Gen</span></div>
        <div class="text-[10px] font-mono text-white/25 mt-0.5">Tree · Queue · Audio</div>
      </div>
    </a>
    <span class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-mono" style="background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.18);color:#22d3ee">
      <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>Web Audio API
    </span>
  </div>
</header>

<main>@yield('content')</main>

<!-- FLOATING PLAYER -->
<div id="floating-player" class="fixed bottom-0 left-0 right-0 z-50 player px-4 pt-2 pb-3 hidden">
  <div class="max-w-[1400px] mx-auto">
    <div class="prog-track mb-2" id="prog-bar" onclick="seekTo(event)">
      <div class="prog-fill" id="prog-fill" style="width:0%"></div>
    </div>
    <div class="flex items-center gap-3">
      <!-- Kontrol -->
      <div class="flex items-center gap-1">
        <button onclick="prevChord()" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/45 hover:text-white" style="background:rgba(255,255,255,.05)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
        </button>
        <button id="play-btn" onclick="togglePlay()" class="w-10 h-10 rounded-xl flex items-center justify-center glow-amber" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#04040a">
          <svg id="play-icon"  width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          <svg id="pause-icon" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="hidden"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
        </button>
        <button onclick="nextChord()" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/45 hover:text-white" style="background:rgba(255,255,255,.05)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
        </button>
        <button onclick="stopPlayer()" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/45 hover:text-white" style="background:rgba(255,255,255,.05)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h12v12H6z"/></svg>
        </button>
        <button id="loop-btn" onclick="toggleLoop()" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/25 hover:text-white" style="background:rgba(255,255,255,.05)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
        </button>
      </div>
      <!-- Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <div class="flex items-end gap-0.5 h-3.5"><span class="wave-bar" style="height:4px;width:2px"></span><span class="wave-bar" style="height:8px;width:2px"></span><span class="wave-bar" style="height:12px;width:2px"></span><span class="wave-bar" style="height:8px;width:2px"></span></div>
          <span id="now-chord" class="font-display font-bold text-amber-400 text-lg leading-none"></span>
          <span id="now-section" class="font-mono text-[10px] text-white/30 truncate"></span>
        </div>
        <div id="now-notes" class="font-mono text-[10px] text-white/18 mt-0.5"></div>
      </div>
      <!-- Meta -->
      <div class="hidden sm:flex items-center gap-4 text-right">
        <div><div class="font-mono text-[9px] text-white/22">Posisi</div><div class="font-mono text-xs text-white/50"><span id="pos-cur">0</span>/<span id="pos-tot">0</span></div></div>
        <div><div class="font-mono text-[9px] text-white/22">BPM</div><div class="font-mono text-xs text-amber-400" id="pl-bpm">-</div></div>
      </div>
    </div>
  </div>
</div>

<!-- AUDIO LOADING OVERLAY -->
<div id="audio-loading">
  <div class="flex items-end gap-1 h-8">
    <span class="wave-bar" style="width:5px"></span><span class="wave-bar" style="width:5px"></span>
    <span class="wave-bar" style="width:5px"></span><span class="wave-bar" style="width:5px"></span>
  </div>
  <div class="font-mono text-xs text-amber-400" id="loading-text">Memuat sampel audio...</div>
</div>

<footer class="border-t border-white/[.04] py-5" style="margin-bottom:72px">
  <div class="max-w-[1400px] mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="font-display text-white/14 text-sm">ChordGen — Laravel + Tone.js Sampler</div>
    <div class="font-mono text-[10px] text-white/14">Binary Tree + FIFO Queue · 4/4 Time Signature</div>
  </div>
</footer>


<script src="https://cdnjs.cloudflare.com/ajax/libs/tone/14.9.11/Tone.js"></script>
<script>
const NOTE_MIDI={
  'C2':36,'D2':38,'E2':40,'F2':41,'G2':43,'A2':45,'B2':47,
  'C3':48,'D3':50,'E3':52,'F3':53,'F#3':54,'G3':55,'A3':57,'A#3':58,'B3':59,
  'C4':60,'C#4':61,'D4':62,'E4':64,'F4':65,'G4':67,'A4':69,'B4':71
};
function m2n(m){const a=['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];return a[m%12]+(Math.floor(m/12)-1);}
function n2t(n){const m=NOTE_MIDI[n];return m?m2n(m):null;}

// Note transform — identik Python Colab
function gNote(n){return n2t(n.replace('4','3'))||n2t(n);}          // Guitar: oktaf turun 1
function bNote(n){return n2t(n.replace('4','2').replace('3','2'))||n2t(n);} // Bass: root C2/D2 range

// Master bus — sangat ringan, tidak ada kompressor heavy
const masterVol=new Tone.Volume(-2).toDestination();

// ── Sampel Tone.js official CDN ───────────────────────────────────────
// Piano: Salamander Grand Piano (rekaman piano konser nyata)
const PS={
  'A0':'A0','C1':'C1','D#1':'Ds1','F#1':'Fs1','A1':'A1',
  'C2':'C2','D#2':'Ds2','F#2':'Fs2','A2':'A2',
  'C3':'C3','D#3':'Ds3','F#3':'Fs3','A3':'A3',
  'C4':'C4','D#4':'Ds4','F#4':'Fs4','A4':'A4',
  'C5':'C5','D#5':'Ds5','F#5':'Fs5','A5':'A5',
  'C6':'C6','D#6':'Ds6','F#6':'Fs6','A6':'A6',
  'C7':'C7','D#7':'Ds7','F#7':'Fs7','A7':'A7','C8':'C8'
};
// Guitar Nylon 
const GS={
  'E2':'E2','F#2':'Fs2','A2':'A2','C3':'C3',
  'E3':'E3','F#3':'Fs3','A3':'A3','C4':'C4',
  'E4':'E4','F#4':'Fs4','A4':'A4','C5':'C5','E5':'E5'
};
// Bass Electric 
const BS={
  'B1':'B1','E1':'E1','A1':'A1',
  'D2':'D2','G2':'G2','C2':'C2','F2':'F2','E2':'E2'
};

const BASE='https://tonejs.github.io/audio/';
function urlMap(map,dir){
  const out={};
  Object.entries(map).forEach(([k,v])=>{out[k]=BASE+dir+'/'+v+'.mp3';});
  return out;
}

// ── Instrumen instances ───────────────────────────────────────────────
let Piano=null, Guitar=null, Bass=null, Strings=null, Pad=null;
let _fx=[];

function disposeFx(){
  _fx.forEach(x=>{try{x.dispose();}catch(e){}});
  _fx=[];
}
function disposeSynths(){
  [Piano,Guitar,Bass,Strings,Pad].forEach(x=>{if(x)try{x.dispose();}catch(e){}});
  Piano=Guitar=Bass=Strings=Pad=null;
}

function mkFx(type,opts){const x=new Tone[type](opts).connect(masterVol);_fx.push(x);return x;}

// Bangun piano — Sampler Salamander
function mkPiano(){
  const rev=new Tone.Reverb({decay:1.6,preDelay:0.008,wet:0.18}).connect(masterVol);
  rev.generate();
  _fx.push(rev);
  return new Promise(res=>{
    Piano=new Tone.Sampler({urls:urlMap(PS,'salamander'),volume:-3,onload:res}).connect(rev);
  });
}

// Bangun guitar — Sampler Nylon
function mkGuitar(){
  const rev=new Tone.Reverb({decay:0.9,preDelay:0.003,wet:0.13}).connect(masterVol);
  const lp=new Tone.Filter({frequency:4000,type:'lowpass'}).connect(rev);
  rev.generate();
  _fx.push(rev,lp);
  return new Promise(res=>{
    Guitar=new Tone.Sampler({urls:urlMap(GS,'guitar-nylon'),volume:-6,onload:res}).connect(lp);
  });
}

// Bangun bass — Sampler Electric
function mkBass(){
  const rev=new Tone.Reverb({decay:0.4,wet:0.05}).connect(masterVol);
  const lp=new Tone.Filter({frequency:450,type:'lowpass',rolloff:-24}).connect(rev);
  rev.generate();
  _fx.push(rev,lp);
  return new Promise(res=>{
    Bass=new Tone.Sampler({urls:urlMap(BS,'bass-electric'),volume:-4,onload:res}).connect(lp);
  });
}

// Bangun strings — synthesis 
function mkStrings(){
  const rev=new Tone.Reverb({decay:2.8,preDelay:0.035,wet:0.40}).connect(masterVol);
  const vib=new Tone.Vibrato({frequency:4.5,depth:0.035,wet:0.5}).connect(rev);
  rev.generate();
  _fx.push(rev,vib);
  Strings={
    a:new Tone.PolySynth(Tone.Synth,{
      maxPolyphony:3,
      oscillator:{type:'sawtooth',detune:-7},
      envelope:{attack:0.28,decay:0.35,sustain:0.88,release:1.8},
      volume:-14
    }).connect(vib),
    b:new Tone.PolySynth(Tone.Synth,{
      maxPolyphony:3,
      oscillator:{type:'sawtooth',detune:7},
      envelope:{attack:0.36,decay:0.35,sustain:0.88,release:1.8},
      volume:-16
    }).connect(vib)
  };
  _fx.push(Strings.a,Strings.b);
  return Promise.resolve();
}

// Bangun pad — synthesis, sangat ringan (sine, polyphony 3)
function mkPad(){
  const rev=new Tone.Reverb({decay:4.0,preDelay:0.05,wet:0.52}).connect(masterVol);
  const cho=new Tone.Chorus({frequency:0.28,delayTime:9,depth:0.65,wet:0.60}).connect(rev);
  cho.start();
  rev.generate();
  _fx.push(rev,cho);
  Pad=new Tone.PolySynth(Tone.Synth,{
    maxPolyphony:3,
    oscillator:{type:'sine'},
    envelope:{attack:0.52,decay:0.45,sustain:0.92,release:2.4},
    volume:-10
  }).connect(cho);
  _fx.push(Pad);
  return Promise.resolve();
}

// ── State ─────────────────────────────────────────────────────────────
const PS_={queue:[],cur:0,bpm:100,insts:[],playing:false,looping:false,_key:''};
let _initP=null;

function showLoading(txt){
  const el=document.getElementById('audio-loading');
  const tx=document.getElementById('loading-text');
  if(txt){el.classList.add('show');if(tx)tx.textContent=txt;}
  else el.classList.remove('show');
}

async function ensureReady(insts){
  const key=[...insts].sort().join(',');
  if(_initP&&PS_._key===key)return _initP;

  // Dispose semua yang lama
  Tone.Transport.cancel();Tone.Transport.stop();
  disposeSynths();disposeFx();
  PS_._key=key;_initP=null;

  const jobs=[];
  const names=[];
  if(insts.includes('Piano'))    {jobs.push(mkPiano());   names.push('Piano');}
  if(insts.includes('Guitar'))   {jobs.push(mkGuitar());  names.push('Guitar');}
  if(insts.includes('Bass'))     {jobs.push(mkBass());    names.push('Bass');}
  if(insts.includes('Strings'))  {jobs.push(mkStrings()); names.push('Strings');}
  if(insts.includes('Synth Pad')){jobs.push(mkPad());     names.push('Pad');}

  showLoading('Memuat '+names.join(', ')+'…');
  _initP=Promise.all(jobs).then(()=>showLoading(null));
  return _initP;
}

function loadData(queue,bpm,insts){
  PS_.queue=queue;PS_.cur=0;PS_.bpm=bpm;PS_.insts=insts;PS_.playing=false;
  document.getElementById('pos-tot').textContent=queue.length;
  document.getElementById('pl-bpm').textContent=bpm;
  document.getElementById('floating-player').classList.remove('hidden');
  updateUI(0);
}

async function togglePlay(){
  await Tone.start();
  PS_.playing?pause_():await play_();
}


async function play_(){
  if(!PS_.queue.length)return;
  showLoading('Menyiapkan audio…');
  await ensureReady(PS_.insts);
  showLoading(null);

  Tone.Transport.cancel();
  Tone.Transport.stop();
  Tone.Transport.bpm.value=PS_.bpm;

  const spb   =60/PS_.bpm;    // detik per beat
  const dur   =spb-0.05;      // durasi note (Python: beat_end = beat_start + spb - 0.05)
  const spc   =4*spb;         // 1 bar = 4 beat
  const si    =PS_.cur;
  const insts =PS_.insts;

  PS_.queue.slice(si).forEach((item,off)=>{
    const gi =si+off;
    const t0 =off*spc;
    const raw=item.not||[];
    if(!raw.length)return;

    // Highlight UI
    Tone.Transport.schedule(time=>{
      Tone.Draw.schedule(()=>updateUI(gi),time);
    },t0);

    // Siapkan array note per instrumen (1x, bukan di dalam loop beat)
    const pNotes =raw.map(n2t).filter(Boolean);          // Piano, Strings, Pad
    const gNotes =raw.map(gNote).filter(Boolean);        // Guitar (oktaf -1)
    const bRoot  =bNote(raw[0]);                         // Bass (root only, C2 range)

    // ── for beat in range(4) 
    for(let b=0;b<4;b++){
      const bt=t0+b*spb;

      if(insts.includes('Piano')&&Piano){
        Tone.Transport.schedule(time=>{
    
          pNotes.forEach(n=>Piano.triggerAttackRelease(n,dur,time));
        },bt);
      }
      if(insts.includes('Guitar')&&Guitar){
        Tone.Transport.schedule(time=>{
          gNotes.forEach(n=>Guitar.triggerAttackRelease(n,dur,time));
        },bt);
      }
      if(insts.includes('Bass')&&Bass&&bRoot){
        Tone.Transport.schedule(time=>{
          Bass.triggerAttackRelease(bRoot,dur,time);
        },bt);
      }
      if(insts.includes('Strings')&&Strings){
        Tone.Transport.schedule(time=>{
  
          Strings.a.triggerAttackRelease(pNotes,dur,time);
          Strings.b.triggerAttackRelease(pNotes,dur,time);
        },bt);
      }
      if(insts.includes('Synth Pad')&&Pad){
        Tone.Transport.schedule(time=>{
          Pad.triggerAttackRelease(pNotes,dur,time);
        },bt);
      }
    }
  });

  // End
  const endT=(PS_.queue.length-si)*spc+0.8;
  Tone.Transport.schedule(time=>{
    Tone.Draw.schedule(()=>{
      if(PS_.looping){Tone.Transport.cancel();Tone.Transport.stop();PS_.cur=0;play_();}
      else stop_();
    },time);
  },endT);

  Tone.Transport.start('+0.1');
  PS_.playing=true;
  setBtn(true);
}

function pause_(){Tone.Transport.pause();PS_.playing=false;setBtn(false);}
function stop_(){
  Tone.Transport.cancel();Tone.Transport.stop();
  PS_.playing=false;PS_.cur=0;setBtn(false);updateUI(0);
}
function stopPlayer(){stop_();}

async function nextChord(){
  const w=PS_.playing;
  if(w){Tone.Transport.cancel();Tone.Transport.stop();PS_.playing=false;}
  PS_.cur=Math.min(PS_.cur+1,PS_.queue.length-1);
  updateUI(PS_.cur);if(w)await play_();
}
async function prevChord(){
  const w=PS_.playing;
  if(w){Tone.Transport.cancel();Tone.Transport.stop();PS_.playing=false;}
  PS_.cur=Math.max(PS_.cur-1,0);
  updateUI(PS_.cur);if(w)await play_();
}
function toggleLoop(){
  PS_.looping=!PS_.looping;
  const b=document.getElementById('loop-btn');
  b.style.color=PS_.looping?'#f59e0b':'';
  b.style.background=PS_.looping?'rgba(245,158,11,.16)':'';
}
async function seekTo(e){
  const pct=Math.max(0,Math.min(1,e.offsetX/e.currentTarget.offsetWidth));
  const idx=Math.floor(pct*PS_.queue.length);
  const w=PS_.playing;
  if(w){Tone.Transport.cancel();Tone.Transport.stop();PS_.playing=false;}
  PS_.cur=Math.max(0,Math.min(idx,PS_.queue.length-1));
  updateUI(PS_.cur);if(w)await play_();
}
function updateUI(idx){
  const q=PS_.queue;if(!q.length)return;
  const item=q[Math.min(idx,q.length-1)];
  PS_.cur=idx;
  document.getElementById('now-chord').textContent  =item.akor||'';
  document.getElementById('now-section').textContent=item.seksi||'';
  document.getElementById('now-notes').textContent  =(item.not||[]).join(' · ');
  document.getElementById('pos-cur').textContent    =idx+1;
  document.getElementById('prog-fill').style.width  =(((idx+1)/q.length)*100)+'%';
  document.querySelectorAll('.chord-card').forEach((el,i)=>el.classList.toggle('active-card',i===idx));
  const ac=document.querySelector('.chord-card.active-card');
  if(ac)ac.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function setBtn(p){
  document.getElementById('play-icon').classList.toggle('hidden',p);
  document.getElementById('pause-icon').classList.toggle('hidden',!p);
}
function updateBpmDisplay(v){const el=document.getElementById('bpm-display');if(el)el.textContent=v+' BPM';}
function updateFamilies(g){
  const map=@json($genreFamily??[]);
  const sel=document.getElementById('family-select');
  if(!sel)return;
  sel.innerHTML='';
  (map[g]||[]).forEach(f=>{const o=document.createElement('option');o.value=o.textContent=f;sel.appendChild(o);});
}
</script>
@stack('scripts')
</body>
</html>
