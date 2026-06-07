<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Chord Generator</title>
 <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎵</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    bg:       '#080910',
                    surface:  '#0e1018',
                    card:     '#12151f',
                    elevated: '#181c28',
                    border:   '#1f2333',
                    borderHi: '#2e3450',
                    accent:   '#00f5a8',
                    accentDim:'#00c987',
                    violet:   '#8b7cf8',
                    amber:    '#f5a623',
                    danger:   '#ff3d5e',
                    text:     '#dde1f0',
                    sub:      '#8891ad',
                    muted:    '#4a5168',
                },
                fontFamily: {
                    display: ['Syne', 'sans-serif'],
                    mono:    ['JetBrains Mono', 'monospace'],
                    body:    ['Outfit', 'sans-serif'],
                },
            }
        }
    }
</script>
<style type="text/tailwindcss">
    @layer utilities {
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #2e3450; border-radius: 8px; }

       
        input[type=range].bpm-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 18px; height: 18px;
            background: #00f5a8;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0,245,168,.5);
            transition: transform .15s, box-shadow .15s;
        }
        input[type=range].bpm-slider::-webkit-slider-thumb:hover {
            transform: scale(1.25);
            box-shadow: 0 0 20px rgba(0,245,168,.7);
        }

        
        input[type=range].seek-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 14px; height: 14px;
            background: #00f5a8;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 8px rgba(0,245,168,.6);
            transition: transform .1s, box-shadow .1s;
        }
        input[type=range].seek-slider::-webkit-slider-thumb:hover {
            transform: scale(1.3);
            box-shadow: 0 0 16px rgba(0,245,168,.8);
        }
        input[type=range].seek-slider::-webkit-slider-runnable-track {
            height: 4px;
            border-radius: 2px;
        }

        .select-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%234a5168' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        .filter-btn.active { @apply text-accent border-accent/40 bg-accent/5; }
        .bar { @apply flex-1 bg-borderHi rounded-full transition-all duration-150; }
        .bar.active { @apply bg-accent; box-shadow: 0 0 6px rgba(0,245,168,.5); }
        .input-field:focus { border-color: #00f5a8; box-shadow: 0 0 0 3px rgba(0,245,168,.08); }
        .section-label { @apply flex items-center gap-2 font-mono text-[10px] tracking-[2px] uppercase text-muted; }
        .section-label::before { content:''; @apply w-1 h-4 rounded-full inline-block bg-accent; box-shadow: 0 0 8px rgba(0,245,168,.6); }
        .inst-label { @apply relative flex items-center gap-3 py-2.5 px-3 bg-surface border border-border rounded-xl cursor-pointer transition-all duration-200 select-none; }
        .inst-label:hover { @apply border-borderHi; }
        .inst-label:has(input:checked) { @apply border-accent/30 bg-accent/5; }

        
        .hist-item { @apply flex items-center gap-3 px-3 py-2.5 rounded-xl border border-border/50 cursor-pointer transition-all duration-150 hover:border-accent/30 hover:bg-accent/5; }
        .hist-item.active { @apply border-accent/40 bg-accent/8; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: slideUp .3s ease forwards; }

        @keyframes shine {
            from { transform: translateX(-100%) skewX(-12deg); }
            to   { transform: translateX(250%) skewX(-12deg); }
        }
        .btn-shine::after {
            content:'';
            position:absolute; inset-y:0; left:-60%; width:40%;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);
            animation: shine 2.5s infinite;
        }

        
        .hist-play-btn {
            @apply w-7 h-7 rounded-full shrink-0 flex items-center justify-center transition-all duration-150;
            background: rgba(0,245,168,.1);
            border: 1px solid rgba(0,245,168,.2);
        }
        .hist-play-btn:hover {
            background: rgba(0,245,168,.2);
            box-shadow: 0 0 10px rgba(0,245,168,.3);
        }
        .hist-play-btn.playing {
            background: rgba(0,245,168,.15);
            border-color: rgba(0,245,168,.5);
        }
    }
</style>
</head>

<body class="bg-bg text-text font-body text-[15px] leading-relaxed min-h-screen relative antialiased selection:bg-accent selection:text-black overflow-x-hidden">

<!-- Background -->
<div class="fixed inset-0 pointer-events-none z-0" aria-hidden="true">
    <div style="background-image:linear-gradient(rgba(0,245,168,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,245,168,.025) 1px,transparent 1px);background-size:56px 56px;width:100%;height:100%;"></div>
    <div style="position:absolute;width:600px;height:600px;top:-100px;left:-200px;background:rgba(0,245,168,.04);border-radius:50%;filter:blur(80px);"></div>
    <div style="position:absolute;width:500px;height:500px;bottom:-80px;right:-100px;background:rgba(139,124,248,.05);border-radius:50%;filter:blur(80px);"></div>
</div>

<div class="max-w-[1320px] mx-auto px-5 lg:px-8 relative z-10 pb-20">

    <!-- HEADER -->
    <header class="py-10 mb-10">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative shrink-0">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl relative z-10"
                         style="background:linear-gradient(135deg,#0e1018,#181c28);border:1px solid #2e3450;box-shadow:0 0 0 1px rgba(0,245,168,.08),inset 0 1px 0 rgba(255,255,255,.04);">🎵</div>
                    <div class="absolute -inset-px rounded-2xl" style="background:linear-gradient(135deg,rgba(0,245,168,.15),transparent,rgba(139,124,248,.1));"></div>
                </div>
                <div>
                    <h1 class="font-display text-[26px] font-bold tracking-tight text-text leading-none" style="font-weight:800;">
                        CHORD <span class="text-accent" style="text-shadow:0 0 24px rgba(0,245,168,.4);">GEN</span>ERATOR
                    </h1>
                    <p class="text-sub text-[12px] mt-1 font-mono tracking-wide">Generate progres chord &amp; audio MP3 otomatis</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-full font-mono text-[11px] tracking-wide text-sub"
                 style="background:#0e1018;border:1px solid #1f2333;box-shadow:inset 0 1px 0 rgba(255,255,255,.03);" id="apiBadge">
                <span class="w-1.5 h-1.5 rounded-full bg-danger animate-pulse transition-colors duration-500 api-dot"></span>
                <span id="apiStatus">Menghubungkan…</span>
            </div>
        </div>
        <div class="mt-8 h-px" style="background:linear-gradient(90deg,transparent,#2e3450 30%,#2e3450 70%,transparent);"></div>
    </header>

  
    <div class="grid grid-cols-1 lg:grid-cols-[260px_360px_1fr] gap-5 items-start">

       
        <div class="lg:sticky lg:top-6">
            <div class="rounded-2xl p-5 relative overflow-hidden"
                 style="background:#0e1018;border:1px solid #1f2333;box-shadow:0 8px 60px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.04);">
                <div class="absolute top-0 left-6 right-6 h-px" style="background:linear-gradient(90deg,transparent,rgba(139,124,248,.2),transparent);"></div>

                <div class="flex items-center justify-between mb-4">
                    <span class="font-mono text-[10px] text-muted tracking-[2px] uppercase">Riwayat Lagu</span>
                    <button onclick="loadHistory()" class="p-1.5 rounded-lg hover:bg-white/5 transition-colors text-muted hover:text-sub" title="Refresh history">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                
                <div class="flex flex-col gap-1.5 max-h-[460px] overflow-y-auto custom-scrollbar pr-0.5" id="historyList">
                    <div class="flex flex-col items-center py-8 text-center gap-2" id="historyEmpty">
                        <div class="text-3xl opacity-20">📋</div>
                        <p class="text-muted text-[11px]">Belum ada riwayat.</p>
                    </div>
                    <div class="flex flex-col gap-1.5" id="historyItems"></div>
                </div>

                
<script id="serverHistory" type="application/json">
<?php
echo json_encode($histories->map(function ($h) {
    return [
        'id' => $h->id,
        'genre' => $h->genre,
        'family' => $h->family,
        'pola' => $h->pola,
        'bpm' => $h->bpm,
        'instruments' => $h->instruments,
        'created_at' => $h->created_at?->diffForHumans(),
        'filename' => $h->result_data['filename'] ?? null,
        'sequence' => $h->result_data['sequence'] ?? [],
        'total_chords' => $h->result_data['total_chords'] ?? 0,
    ];
}));
?>
</script>
            </div>
        </div>

       
        <div class="lg:sticky lg:top-6">
            <div class="rounded-2xl p-6 relative overflow-hidden"
                 style="background:#0e1018;border:1px solid #1f2333;box-shadow:0 8px 60px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.04);">
                <div class="absolute top-0 left-8 right-8 h-px" style="background:linear-gradient(90deg,transparent,rgba(0,245,168,.2),transparent);"></div>

                <div class="section-label mb-6">Konfigurasi Lagu</div>

                <div class="mb-5">
                    <label class="block text-[11px] font-mono text-sub mb-2 tracking-widest uppercase">Genre</label>
                    <select id="genreSelect" onchange="updateFamilies()"
                        class="input-field w-full rounded-xl text-text font-body text-sm py-2.5 px-4 pr-10 outline-none transition-all appearance-none cursor-pointer select-icon"
                        style="background:#080910;border:1px solid #1f2333;">
                        @foreach($options['genres'] as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-[11px] font-mono text-sub mb-2 tracking-widest uppercase">Chord Family</label>
                    <select id="familySelect"
                        class="input-field w-full rounded-xl text-text font-body text-sm py-2.5 px-4 pr-10 outline-none transition-all appearance-none cursor-pointer select-icon"
                        style="background:#080910;border:1px solid #1f2333;"></select>
                </div>

                <div class="mb-5">
                    <label class="block text-[11px] font-mono text-sub mb-2 tracking-widest uppercase">Pola Struktur</label>
                    <select id="polaSelect"
                        class="input-field w-full rounded-xl text-text font-body text-sm py-2.5 px-4 pr-10 outline-none transition-all appearance-none cursor-pointer select-icon"
                        style="background:#080910;border:1px solid #1f2333;">
                        @foreach($options['polas'] as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-[11px] font-mono text-sub tracking-widest uppercase">BPM / Tempo</label>
                        <span class="font-mono text-[13px] text-accent font-bold px-2.5 py-0.5 rounded-md"
                              style="background:rgba(0,245,168,.08);border:1px solid rgba(0,245,168,.15);" id="bpmVal">{{ $options['bpm_range']['default'] }} BPM</span>
                    </div>
                    <input type="range" id="bpmRange"
                           min="{{ $options['bpm_range']['min'] }}"
                           max="{{ $options['bpm_range']['max'] }}"
                           value="{{ $options['bpm_range']['default'] }}"
                           oninput="document.getElementById('bpmVal').textContent = this.value + ' BPM'; updateSliderTrack(this)"
                           class="bpm-slider w-full h-1 rounded-full appearance-none outline-none cursor-pointer">
                    <div class="flex justify-between mt-1.5">
                        <span class="font-mono text-[10px] text-muted">{{ $options['bpm_range']['min'] }}</span>
                        <span class="font-mono text-[10px] text-muted">{{ $options['bpm_range']['max'] }}</span>
                    </div>
                </div>

                <div class="mb-7">
                    <label class="block text-[11px] font-mono text-sub mb-3 tracking-widest uppercase">Instrumen</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($options['instruments'] as $inst)
                            <label class="inst-label">
                                <input type="checkbox" name="instruments[]" value="{{ $inst }}" class="peer hidden"
                                       {{ in_array($inst, ['Piano','Bass','Guitar']) ? 'checked' : '' }}>
                                <div class="w-4 h-4 rounded-md flex items-center justify-center transition-all shrink-0 border-[1.5px] peer-checked:bg-accent peer-checked:border-accent" style="border-color:#2e3450;">
                                    <svg class="w-2.5 h-2.5 text-black peer-checked:block hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-[13px] font-medium peer-checked:text-accent transition-colors">{{ $inst }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button id="generateBtn" onclick="generateMusic()"
                    class="btn-shine relative w-full py-4 rounded-xl font-display font-bold text-[13px] tracking-[2px] uppercase text-black transition-all duration-200 overflow-hidden disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2.5"
                    style="background:linear-gradient(135deg,#00f5a8,#00c987);box-shadow:0 4px 24px rgba(0,245,168,.3);">
                    <span id="btnIcon">▶</span>
                    <span id="btnText">GENERATE MUSIK</span>
                </button>

                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-32 h-px pointer-events-none"
                     style="background:linear-gradient(90deg,transparent,rgba(0,245,168,.3),transparent);"></div>
            </div>
        </div>

  
        <div class="flex flex-col gap-5">

            
            <div class="rounded-2xl p-8 flex flex-col items-center justify-center min-h-[300px] text-center"
                 id="emptyState"
                 style="background:#0e1018;border:1px solid #1f2333;border-style:dashed;">
                <div class="text-6xl mb-5 opacity-20">🎹</div>
                <p class="text-sub text-sm max-w-[220px] leading-relaxed">
                    Pilih konfigurasi dan klik <span class="text-accent font-mono">Generate</span> untuk membuat lagu.
                </p>
            </div>

          
            <div class="rounded-2xl p-8 flex flex-col items-center justify-center min-h-[300px]"
                 id="loadingState" style="display:none;background:#0e1018;border:1px solid #1f2333;">
                <div class="relative w-16 h-16 mb-6">
                    <div class="absolute inset-0 rounded-full animate-spin"
                         style="border:2px solid transparent;border-top-color:#00f5a8;border-right-color:rgba(0,245,168,.3);"></div>
                    <div class="absolute inset-2 rounded-full animate-spin"
                         style="border:2px solid transparent;border-top-color:rgba(139,124,248,.6);animation-direction:reverse;animation-duration:1.2s;"></div>
                    <div class="absolute inset-4 rounded-full bg-accent/10 flex items-center justify-center text-lg">🎵</div>
                </div>
                <p class="text-sub text-sm font-mono animate-pulse" id="loadingMsg">Menyusun progres chord…</p>
            </div>

           
            <div id="playerCard" style="display:none;">
                <div class="rounded-2xl p-6 relative overflow-hidden animate-in"
                     style="background:linear-gradient(135deg,#0e1018 0%,#12151f 100%);border:1px solid #1f2333;box-shadow:0 8px 60px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.04);">
                    <div class="absolute top-0 right-0 w-48 h-48 pointer-events-none"
                         style="background:radial-gradient(circle at top right,rgba(0,245,168,.06),transparent 70%);"></div>

                    <div class="section-label mb-5">Now Playing</div>

                    
                    <div class="flex items-center gap-4 mb-5">
                       
                        <button id="playBtn" onclick="togglePlay()"
                            class="relative w-14 h-14 rounded-full shrink-0 flex items-center justify-center transition-all duration-200 hover:scale-105"
                            style="background:linear-gradient(135deg,#00f5a8,#00c987);box-shadow:0 4px 20px rgba(0,245,168,.35);">
                            <svg id="playIcon" width="18" height="18" viewBox="0 0 24 24" class="fill-black ml-0.5"><path d="M8 5v14l11-7z"/></svg>
                        </button>

                        
                        <div class="flex-1 min-w-0">
                            <div class="font-mono text-[10px] text-muted truncate uppercase tracking-widest" id="playerFilename">—</div>
                            <div class="text-[14px] text-text font-semibold mt-1 truncate" id="playerMeta">—</div>
                        </div>

                        <!-- Download button -->
                        <!-- <button id="downloadBtn" onclick="downloadAudio()"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-mono text-[11px] text-sub hover:text-accent transition-all shrink-0"
                            style="background:#080910;border:1px solid #1f2333;">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span id="downloadBtnText">UNDUH</span>
                        </button> -->
                    </div>

                
                    <div class="mb-4">
                       
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-mono text-[10px] text-accent" id="timeCurrentLabel">0:00</span>
                            <span class="font-mono text-[10px] text-muted" id="timeTotalLabel">0:00</span>
                        </div>

                        
                        <div class="relative w-full group">
                            <input type="range"
                                   id="seekBar"
                                   class="seek-slider w-full h-1 rounded-full appearance-none outline-none cursor-pointer"
                                   min="0" max="100" step="0.1" value="0"
                                   style="background:linear-gradient(90deg, #00f5a8 0%, #1f2333 0%);"
                                   oninput="onSeekInput(this)"
                                   onmousedown="seekDragging=true"
                                   onmouseup="onSeekRelease(this)"
                                   ontouchend="onSeekRelease(this)"
                                   onchange="onSeekRelease(this)">
                        </div>

                     
                        <div class="flex justify-between mt-1.5">
                            <span class="font-mono text-[10px] text-muted">0:00</span>
                            <span class="font-mono text-[10px] text-muted" id="seekSpeedLabel" style="display:none;"></span>
                        </div>
                    </div>

               
                    <div class="w-full h-10 flex items-center gap-[2px]" id="waveformBars"></div>

                
                    <audio id="audioPlayer"
                           onended="onAudioEnd()"
                           ontimeupdate="onAudioTimeUpdate()"
                           onloadedmetadata="onAudioMetaLoaded()"
                           oncanplay="onAudioCanPlay()"
                           class="hidden"></audio>
                </div>
            </div>

            
            <div class="grid grid-cols-3 gap-4 animate-in" id="statsBar" style="display:none">
                <div class="rounded-xl p-4 text-center relative overflow-hidden"
                     style="background:#0e1018;border:1px solid #1f2333;">
                    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(circle at 50% 0%,rgba(0,245,168,.04),transparent 70%);"></div>
                    <div class="font-display font-bold text-[28px] text-accent leading-none" id="statChords"
                         style="text-shadow:0 0 20px rgba(0,245,168,.4);">—</div>
                    <div class="text-[10px] font-mono text-muted mt-2 tracking-widest uppercase">Chord</div>
                </div>
                <div class="rounded-xl p-4 text-center relative overflow-hidden"
                     style="background:#0e1018;border:1px solid #1f2333;">
                    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(circle at 50% 0%,rgba(139,124,248,.04),transparent 70%);"></div>
                    <div class="font-display font-bold text-[28px] text-violet leading-none" id="statBPM"
                         style="text-shadow:0 0 20px rgba(139,124,248,.3);">—</div>
                    <div class="text-[10px] font-mono text-muted mt-2 tracking-widest uppercase">BPM</div>
                </div>
                <div class="rounded-xl p-4 text-center relative overflow-hidden"
                     style="background:#0e1018;border:1px solid #1f2333;">
                    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(circle at 50% 0%,rgba(245,166,35,.04),transparent 70%);"></div>
                    <div class="font-display font-bold text-[28px] text-amber leading-none" id="statInst"
                         style="text-shadow:0 0 20px rgba(245,166,35,.3);">—</div>
                    <div class="text-[10px] font-mono text-muted mt-2 tracking-widest uppercase">Instrumen</div>
                </div>
            </div>

           
            <div id="sequenceCard" style="display:none;">
                <div class="rounded-2xl p-6 animate-in"
                     style="background:#0e1018;border:1px solid #1f2333;box-shadow:0 8px 60px rgba(0,0,0,.5);">
                    <div class="flex items-center justify-between mb-5">
                        <div class="section-label">Urutan Chord</div>
                        <div class="flex gap-1 p-1 rounded-full" style="background:#080910;border:1px solid #1f2333;">
                            <button class="filter-btn px-4 py-1.5 rounded-full text-[10px] font-mono tracking-wide transition-all border border-transparent"
                                    onclick="filterTable('all', this)">Semua</button>
                            <button class="filter-btn px-4 py-1.5 rounded-full text-[10px] font-mono tracking-wide transition-all border border-transparent"
                                    onclick="filterTable('verse', this)">Verse</button>
                            <button class="filter-btn px-4 py-1.5 rounded-full text-[10px] font-mono tracking-wide transition-all border border-transparent"
                                    onclick="filterTable('reff', this)">Reff</button>
                        </div>
                    </div>
                    <div class="rounded-xl overflow-hidden border custom-scrollbar max-h-[340px] overflow-y-auto" style="border-color:#1f2333;">
                        <table class="w-full text-left border-collapse">
                            <thead style="background:#12151f;position:sticky;top:0;z-index:10;">
                                <tr>
                                    <th class="w-10 py-3 px-4 text-[10px] uppercase tracking-widest text-muted font-mono font-medium border-b" style="border-color:#1f2333;">#</th>
                                    <th class="py-3 px-4 text-[10px] uppercase tracking-widest text-muted font-mono font-medium border-b" style="border-color:#1f2333;">Seksi</th>
                                    <th class="py-3 px-4 text-[10px] uppercase tracking-widest text-muted font-mono font-medium border-b" style="border-color:#1f2333;">Chord</th>
                                    <th class="py-3 px-4 text-[10px] uppercase tracking-widest text-muted font-mono font-medium border-b" style="border-color:#1f2333;">Notasi</th>
                                </tr>
                            </thead>
                            <tbody id="sequenceBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="toast"
     class="fixed bottom-6 right-6 z-[999] flex items-center gap-3 px-5 py-3.5 rounded-xl text-sm font-medium transition-all duration-300 translate-y-6 opacity-0 pointer-events-none"
     style="background:#12151f;border:1px solid rgba(255,61,94,.3);color:#ff3d5e;box-shadow:0 8px 40px rgba(0,0,0,.6);">
    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <span id="toastMsg">Error</span>
</div>

<div id="toastSuccess"
     class="fixed bottom-6 right-6 z-[999] flex items-center gap-3 px-5 py-3.5 rounded-xl text-sm font-medium transition-all duration-300 translate-y-6 opacity-0 pointer-events-none"
     style="background:#12151f;border:1px solid rgba(0,245,168,.3);color:#00f5a8;box-shadow:0 8px 40px rgba(0,0,0,.6);">
    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    <span id="toastSuccessMsg">OK</span>
</div>

<script>

const OPTIONS       = @json($options);
const CSRF          = document.querySelector('meta[name="csrf-token"]').content;
const HISTORY_URL   = '{{ route("music.history.list") }}';
const DELETE_URL    = '{{ url("music/history") }}';
const AUDIO_BASE    = '{{ url("music/audio") }}';

let audioUrl         = null;   
let downloadFilename = null;   
let isPlaying        = false;
let seekDragging     = false;  
let allSequence      = [];
let barAnimInterval  = null;
let historyData      = [];
let activeHistId     = null;


document.addEventListener('DOMContentLoaded', () => {
    updateFamilies();
    buildWaveform();
    checkApiStatus();
    setInterval(checkApiStatus, 15000);
    initSliderTrack();


    const firstFilter = document.querySelector('.filter-btn');
    if (firstFilter) firstFilter.classList.add('active');


    try {
        const raw = JSON.parse(document.getElementById('serverHistory').textContent);
        historyData = raw;
        renderHistoryList();
    } catch(e) { console.warn('History parse error', e); }
});


function initSliderTrack() {
    const r = document.getElementById('bpmRange');
    updateSliderTrack(r);
}
function updateSliderTrack(r) {
    const pct = ((r.value - r.min) / (r.max - r.min)) * 100;
    r.style.background = `linear-gradient(90deg, #00f5a8 ${pct}%, #1f2333 ${pct}%)`;
}

function updateFamilies() {
    const genre = document.getElementById('genreSelect').value;
    const sel   = document.getElementById('familySelect');
    const fams  = OPTIONS.families[genre] || [];
    sel.innerHTML = fams.map(f => `<option value="${f}">${f}</option>`).join('');
}


function buildWaveform() {
    const c = document.getElementById('waveformBars');
    c.innerHTML = '';
    for (let i = 0; i < 56; i++) {
        const bar = document.createElement('div');
        bar.className = 'bar';
        bar.style.height = (12 + Math.random() * 88) + '%';
        bar.style.borderRadius = '2px';
        c.appendChild(bar);
    }
}

async function checkApiStatus() {
    const badge = document.getElementById('apiBadge');
    const dot   = badge.querySelector('.api-dot');
    const span  = document.getElementById('apiStatus');
    try {
        const r = await fetch('/music/api-status', { signal: AbortSignal.timeout(5000) });
        const d = await r.json();
        if (d.online) {
            dot.style.background = '#00f5a8';
            dot.style.boxShadow  = '0 0 6px #00f5a8';
            span.textContent     = 'API Online';
        } else { dot.style.background = '#ff3d5e'; dot.style.boxShadow = 'none'; span.textContent = 'API Offline'; }
    } catch { dot.style.background = '#ff3d5e'; span.textContent = 'API Offline'; }
}

async function generateMusic() {
    const instruments = [...document.querySelectorAll('input[name="instruments[]"]:checked')].map(i => i.value);
    if (instruments.length === 0) { showToast('Pilih minimal satu instrumen!'); return; }

    const payload = {
        genre:       document.getElementById('genreSelect').value,
        family:      document.getElementById('familySelect').value,
        pola:        document.getElementById('polaSelect').value,
        instruments: instruments,
        bpm:         parseInt(document.getElementById('bpmRange').value),
    };

    setLoading(true);
    const msgs = ['Menyusun progres chord…','Merender MIDI…','Mengekspor MP3…','Hampir selesai…'];
    let mi = 0;
    const mi2 = setInterval(() => { document.getElementById('loadingMsg').textContent = msgs[(++mi) % msgs.length]; }, 2000);

    try {
        const res    = await fetch('/music/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const result = await res.json();
        clearInterval(mi2);

        if (result.success) {
            showResult(result.data, payload);
            await loadHistory();
        } else {
            showToast(result.message || 'Generate gagal');
            setLoading(false);
        }
    } catch {
        clearInterval(mi2);
        showToast('Tidak dapat terhubung ke server. Cek koneksi.');
        setLoading(false);
    }
}

function setLoading(on) {
    document.getElementById('generateBtn').disabled = on;
    document.getElementById('btnText').textContent   = on ? 'MEMPROSES…' : 'GENERATE MUSIK';
    document.getElementById('btnIcon').innerHTML     = on
        ? '<svg class="animate-spin h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
        : '▶';
    document.getElementById('emptyState').style.display   = 'none';
    document.getElementById('loadingState').style.display = on ? 'block' : 'none';
    document.getElementById('playerCard').style.display   = on ? 'none' : '';
    document.getElementById('statsBar').style.display     = on ? 'none' : '';
    document.getElementById('sequenceCard').style.display = on ? 'none' : '';
}

function showResult(data, payload) {
    setLoading(false);
    buildWaveform();
    activeHistId = data.history_id ?? null;

    // Load audio
    loadAudioSrc(data.mp3_url, data.filename);
    document.getElementById('playerFilename').textContent = data.filename;
    document.getElementById('playerMeta').textContent     = `${payload.genre} · ${payload.family} · ${payload.pola}`;
    document.getElementById('playerCard').style.display   = 'block';


    document.getElementById('statChords').textContent = data.total_chords;
    document.getElementById('statBPM').textContent    = payload.bpm;
    document.getElementById('statInst').textContent   = payload.instruments.length;
    document.getElementById('statsBar').style.display = 'grid';


    renderTable(data.sequence);
    document.getElementById('sequenceCard').style.display = 'block';
}

function loadAudioSrc(url, filename) {
    const audio = document.getElementById('audioPlayer');


    if (isPlaying) {
        audio.pause();
        isPlaying = false;
        clearInterval(barAnimInterval);
    }

    audioUrl         = url;
    downloadFilename = filename;


    resetSeekUI();


    document.getElementById('playIcon').innerHTML = '<path d="M8 5v14l11-7z"/>';


    audio.src = url;
    audio.load();
}

function resetSeekUI() {
    const seekBar = document.getElementById('seekBar');
    seekBar.value = 0;
    seekBar.style.background = 'linear-gradient(90deg, #00f5a8 0%, #1f2333 0%)';
    document.getElementById('timeCurrentLabel').textContent = '0:00';
    document.getElementById('timeTotalLabel').textContent   = '0:00';
    document.querySelectorAll('#waveformBars .bar').forEach(b => b.classList.remove('active'));
}

function togglePlay() {
    const audio    = document.getElementById('audioPlayer');
    const playIcon = document.getElementById('playIcon');
    if (!audio.src || audio.src === window.location.href) return;

    if (isPlaying) {
        audio.pause();
        isPlaying = false;
        playIcon.innerHTML = '<path d="M8 5v14l11-7z"/>';
        document.querySelectorAll('#waveformBars .bar').forEach(b => b.classList.remove('active'));
        clearInterval(barAnimInterval);
   
        syncHistPlayBtn(activeHistId, false);
    } else {
        audio.play().catch(err => {
            showToast('Tidak dapat memutar audio: ' + err.message);
        });
        isPlaying = true;
        playIcon.innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
        animateBars();
        syncHistPlayBtn(activeHistId, true);
    }
}

function animateBars() {
    clearInterval(barAnimInterval);
    barAnimInterval = setInterval(() => {
        if (!isPlaying) { clearInterval(barAnimInterval); return; }
        document.querySelectorAll('#waveformBars .bar').forEach(b => b.classList.toggle('active', Math.random() > 0.45));
    }, 120);
}

function onAudioEnd() {
    isPlaying = false;
    document.getElementById('playIcon').innerHTML = '<path d="M8 5v14l11-7z"/>';
    document.querySelectorAll('#waveformBars .bar').forEach(b => b.classList.remove('active'));
    clearInterval(barAnimInterval);
    syncHistPlayBtn(activeHistId, false);

    
    const seekBar = document.getElementById('seekBar');
    seekBar.value = 0;
    seekBar.style.background = 'linear-gradient(90deg, #00f5a8 0%, #1f2333 0%)';
    document.getElementById('timeCurrentLabel').textContent = '0:00';
}


function onAudioMetaLoaded() {
    const audio = document.getElementById('audioPlayer');
    const dur   = audio.duration;
    if (!isNaN(dur) && isFinite(dur)) {
        document.getElementById('timeTotalLabel').textContent = formatTime(dur);
    }
}

function onAudioCanPlay() {
  
    onAudioMetaLoaded();
}

function onAudioTimeUpdate() {
    if (seekDragging) return; 

    const audio   = document.getElementById('audioPlayer');
    const seekBar = document.getElementById('seekBar');
    const current = audio.currentTime;
    const dur     = audio.duration;

    if (!isNaN(dur) && dur > 0) {
        const pct = (current / dur) * 100;
        seekBar.value = pct;
        updateSeekBarFill(pct);
        document.getElementById('timeCurrentLabel').textContent = formatTime(current);
        document.getElementById('timeTotalLabel').textContent   = formatTime(dur);
    }
}


function onSeekInput(input) {
    updateSeekBarFill(parseFloat(input.value));

    const audio = document.getElementById('audioPlayer');
    const dur   = audio.duration;
    if (!isNaN(dur) && dur > 0) {
        const previewTime = (parseFloat(input.value) / 100) * dur;
        document.getElementById('timeCurrentLabel').textContent = formatTime(previewTime);
    }
}

function onSeekRelease(input) {
    seekDragging = false;
    const audio = document.getElementById('audioPlayer');
    const dur   = audio.duration;
    if (!isNaN(dur) && dur > 0 && isFinite(dur)) {
        audio.currentTime = (parseFloat(input.value) / 100) * dur;
    }
}

function updateSeekBarFill(pct) {
    const seekBar = document.getElementById('seekBar');
    seekBar.style.background = `linear-gradient(90deg, #00f5a8 ${pct}%, #1f2333 ${pct}%)`;
}

function formatTime(secs) {
    if (isNaN(secs) || !isFinite(secs)) return '0:00';
    const m = Math.floor(secs / 60);
    const s = Math.floor(secs % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

async function downloadAudio() {
    if (!audioUrl || !downloadFilename) return;
    const btn  = document.getElementById('downloadBtn');
    const text = document.getElementById('downloadBtnText');

    btn.disabled     = true;
    text.textContent = 'MENGUNDUH…';

    try {

        const dlUrl = audioUrl + (audioUrl.includes('?') ? '&' : '?') + 'dl=1';
        const resp  = await fetch(dlUrl);
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const blob   = await resp.blob();
        const objUrl = URL.createObjectURL(blob);
        const a      = document.createElement('a');
        a.href       = objUrl;
        a.download   = downloadFilename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(objUrl);
        showToastSuccess('File berhasil diunduh!');
    } catch (err) {
        showToast('Gagal mengunduh: ' + err.message);
    } finally {
        btn.disabled     = false;
        text.textContent = 'UNDUH';
    }
}

function renderTable(seq) {
    allSequence = seq;
    filterTable('all', document.querySelector('.filter-btn'));
}

function filterTable(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filtered = type === 'all' ? allSequence
        : allSequence.filter(i => i.seksi.toLowerCase().includes(type));

    document.getElementById('sequenceBody').innerHTML = filtered.map((item, idx) => {
        const isReff    = item.seksi.toLowerCase().includes('reff');
        const pillBg    = isReff ? 'rgba(139,124,248,.12)' : 'rgba(0,245,168,.1)';
        const pillColor = isReff ? '#8b7cf8' : '#00f5a8';
        const pillBdr   = isReff ? 'rgba(139,124,248,.25)' : 'rgba(0,245,168,.2)';
        const rowBg     = idx % 2 === 0 ? 'transparent' : 'rgba(255,255,255,.015)';
        const notes     = Array.isArray(item.notes) ? item.notes.join(' · ') : '';
        return `<tr style="background:${rowBg};" onmouseover="this.style.background='rgba(0,245,168,.03)'" onmouseout="this.style.background='${rowBg}'">
            <td class="py-3 px-4 font-mono text-[11px] text-muted">${idx + 1}</td>
            <td class="py-3 px-4 text-[12px] text-sub font-medium">${item.seksi}</td>
            <td class="py-3 px-4"><span class="inline-block px-3 py-1 rounded-lg font-mono text-[11px] font-bold" style="background:${pillBg};color:${pillColor};border:1px solid ${pillBdr};">${item.akor}</span></td>
            <td class="py-3 px-4 font-mono text-[11px] text-muted">${notes}</td>
        </tr>`;
    }).join('');
}

async function loadHistory() {
    try {
        const res  = await fetch(HISTORY_URL);
        const data = await res.json();
        historyData = data.histories || [];
        renderHistoryList();
    } catch(e) { console.warn('Load history failed', e); }
}

function renderHistoryList() {
    const items = document.getElementById('historyItems');
    const empty = document.getElementById('historyEmpty');
    items.innerHTML = '';

    if (!historyData.length) {
        empty.style.display = 'flex';
        return;
    }
    empty.style.display = 'none';

    historyData.forEach(h => {
        const isActive = h.id === activeHistId;
        const div = document.createElement('div');
        div.className = 'hist-item group' + (isActive ? ' active' : '');
        div.dataset.id = h.id;

  
        const isThisPlaying = (h.id === activeHistId && isPlaying);

        div.innerHTML = `
            <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center text-[13px]"
                 style="background:rgba(0,245,168,.08);border:1px solid rgba(0,245,168,.15);">🎵</div>
            <div class="flex-1 min-w-0">
                <div class="text-[12px] font-medium text-text truncate">${h.genre} · ${h.family}</div>
                <div class="text-[10px] text-muted mt-0.5 font-mono">${h.pola} · ${h.bpm} BPM</div>
                <div class="text-[10px] text-muted/70 mt-0.5">${h.created_at}</div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                ${h.filename ? `
                <button class="hist-play-btn${isThisPlaying ? ' playing' : ''}"
                        id="histPlayBtn-${h.id}"
                        onclick="event.stopPropagation(); historyTogglePlay(${h.id})"
                        title="${isThisPlaying ? 'Pause' : 'Putar'}">
                    ${isThisPlaying
                        ? `<svg width="10" height="10" viewBox="0 0 24 24" fill="#00f5a8"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`
                        : `<svg width="10" height="10" viewBox="0 0 24 24" fill="#00f5a8"><path d="M8 5v14l11-7z"/></svg>`
                    }
                </button>` : ''}
                <button onclick="event.stopPropagation();deleteHistoryItem(${h.id})"
                    class="opacity-0 group-hover:opacity-100 p-1 rounded-lg hover:bg-danger/10 hover:text-danger text-muted transition-all">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>`;


        div.addEventListener('click', () => loadHistoryResult(h));
        items.appendChild(div);
    });
}

function historyTogglePlay(histId) {
    const h = historyData.find(x => x.id === histId);
    if (!h || !h.filename) return;

    const targetUrl = `${AUDIO_BASE}/${h.filename}`;

    if (activeHistId === histId) {
    
        togglePlay();
    } else {
      
        loadHistoryResult(h, true); 
    }
}


function loadHistoryResult(h, autoPlay = false) {
    activeHistId = h.id;

  
    document.querySelectorAll('.hist-item').forEach(el => {
        el.classList.toggle('active', parseInt(el.dataset.id) === h.id);
    });

    renderTable(h.sequence || []);
    document.getElementById('sequenceCard').style.display = 'block';

    document.getElementById('statChords').textContent = h.total_chords || h.sequence?.length || '—';
    document.getElementById('statBPM').textContent    = h.bpm;
    document.getElementById('statInst').textContent   = (h.instruments || []).length;
    document.getElementById('statsBar').style.display = 'grid';

    document.getElementById('emptyState').style.display   = 'none';
    document.getElementById('loadingState').style.display = 'none';

    if (h.filename) {
        const url = `${AUDIO_BASE}/${h.filename}`;

       
        loadAudioSrc(url, h.filename);

        document.getElementById('playerFilename').textContent = h.filename;
        document.getElementById('playerMeta').textContent     = `${h.genre} · ${h.family} · ${h.pola}`;
        document.getElementById('playerCard').style.display   = 'block';
        buildWaveform();

        if (autoPlay) {
            
            const audio = document.getElementById('audioPlayer');
            const onReady = () => {
                audio.removeEventListener('canplay', onReady);
                audio.play().then(() => {
                    isPlaying = true;
                    document.getElementById('playIcon').innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
                    animateBars();
                    syncHistPlayBtn(activeHistId, true);
                }).catch(err => showToast('Gagal memutar: ' + err.message));
            };
            audio.addEventListener('canplay', onReady);
        }
    } else {
        document.getElementById('playerCard').style.display = 'none';
    }


    renderHistoryList();
}


function syncHistPlayBtn(histId, playing) {
    const btn = document.getElementById(`histPlayBtn-${histId}`);
    if (!btn) return;
    btn.classList.toggle('playing', playing);
    btn.title = playing ? 'Pause' : 'Putar';
    btn.innerHTML = playing
        ? `<svg width="10" height="10" viewBox="0 0 24 24" fill="#00f5a8"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`
        : `<svg width="10" height="10" viewBox="0 0 24 24" fill="#00f5a8"><path d="M8 5v14l11-7z"/></svg>`;
}

async function deleteHistoryItem(id) {
    if (!confirm('Hapus riwayat ini?')) return;
    try {
        const res = await fetch(`${DELETE_URL}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
        if (res.ok) {
            if (activeHistId === id) {
              
                const audio = document.getElementById('audioPlayer');
                audio.pause();
                isPlaying = false;
                clearInterval(barAnimInterval);
                document.getElementById('playerCard').style.display = 'none';
                activeHistId = null;
            }
            historyData = historyData.filter(h => h.id !== id);
            renderHistoryList();
            showToastSuccess('Riwayat dihapus.');
        }
    } catch { showToast('Gagal menghapus riwayat.'); }
}

function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.style.transform = 'translateY(0)'; t.style.opacity = '1'; t.style.pointerEvents = 'auto';
    setTimeout(() => { t.style.transform = 'translateY(24px)'; t.style.opacity = '0'; t.style.pointerEvents = 'none'; }, 4000);
}
function showToastSuccess(msg) {
    const t = document.getElementById('toastSuccess');
    document.getElementById('toastSuccessMsg').textContent = msg;
    t.style.transform = 'translateY(0)'; t.style.opacity = '1'; t.style.pointerEvents = 'auto';
    setTimeout(() => { t.style.transform = 'translateY(24px)'; t.style.opacity = '0'; t.style.pointerEvents = 'none'; }, 3000);
}
</script>
</body>
</html>
