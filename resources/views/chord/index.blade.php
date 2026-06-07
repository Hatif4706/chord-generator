@extends('layouts.app')
@section('title','ChordGen — Generator')

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden pt-12 pb-5">
    <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(245,158,11,.05) 0%,transparent 70%);transform:translate(-50%,-30%)"></div>
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 text-center animate-slide-up">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-mono mb-4" style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.14);color:rgba(245,158,11,.65)">
            ♪ Chord Progression Generator — Tree & Queue & Audio
        </div>
        <h1 class="font-display font-bold text-4xl sm:text-[48px] text-white mb-3 tracking-tight leading-tight">
            Generate <span class="text-amber-400 text-glow">Chord</span> <span class="text-white/35">Progression</span>
        </h1>
        <p class="text-white/30 font-body max-w-lg mx-auto text-sm leading-relaxed">
            <span class="text-cyan-400/60 font-mono">Binary Tree</span> &nbsp;·&nbsp;
            <span class="text-amber-400/60 font-mono">FIFO Queue</span> &nbsp;·&nbsp;
            <span class="text-green-400/60 font-mono">Sampler Audio</span>
        </p>
    </div>
</section>

{{-- ERRORS --}}
@if($errors->any())
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 mb-4">
    <div class="glass rounded-xl p-4" style="border-left:3px solid #f43f5e">
        @foreach($errors->all() as $e)<p class="text-rose-300 text-sm">{{ $e }}</p>@endforeach
    </div>
</div>
@endif

{{-- ══ MAIN GRID ══ --}}
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 pb-8">
<div class="grid grid-cols-1 xl:grid-cols-4 gap-5">

{{-- ══════════════════════════════
     COL 1 — SIDEBAR
     Form: sticky di bawah header.
     History: mengalir normal di bawahnya (tidak sticky),
     sehingga TIDAK pernah menutupi tombol Generate.
══════════════════════════════ --}}
<div class="xl:col-span-1 col-sidebar">

    {{-- FORM (sticky) --}}
    <div class="form-sticky">
        <div class="glass rounded-2xl overflow-hidden form-inner">
            <div class="px-5 py-3.5" style="background:rgba(245,158,11,.04);border-bottom:1px solid rgba(245,158,11,.09)">
                <h2 class="font-display font-semibold text-white text-sm">⚙️ Parameter Generator</h2>
                <p class="text-white/25 text-[10px] font-mono mt-0.5">Konfigurasi input sistem</p>
            </div>
            <form action="{{ route('chord.generate') }}" method="POST" class="p-4 flex flex-col gap-4">
                @csrf

                {{-- Genre --}}
                <div>
                    <label class="block text-[10px] font-mono text-white/35 mb-1.5 uppercase tracking-widest">Genre</label>
                    <select name="genre" id="genre-select" onchange="updateFamilies(this.value)"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-white appearance-none cursor-pointer focus:outline-none pr-7"
                            style="background:#0d0d1a;border:1px solid rgba(255,255,255,.08)">
                        @foreach($genreFamily as $g => $fams)
                        <option value="{{ $g }}" {{ old('genre',$input['genre']??'Pop')===$g?'selected':'' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Family --}}
                <div>
                    <label class="block text-[10px] font-mono text-white/35 mb-1.5 uppercase tracking-widest">Keluarga Chord</label>
                    <select name="family" id="family-select"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-white appearance-none cursor-pointer focus:outline-none pr-7"
                            style="background:#0d0d1a;border:1px solid rgba(255,255,255,.08)">
                        @php $sg=old('genre',$input['genre']??'Pop');$sf=old('family',$input['family']??''); @endphp
                        @foreach($genreFamily[$sg]??[] as $fam)
                        <option value="{{ $fam }}" {{ $sf===$fam?'selected':'' }}>{{ $fam }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pola --}}
                <div>
                    <label class="block text-[10px] font-mono text-white/35 mb-1.5 uppercase tracking-widest">Pola Struktur</label>
                    <div class="space-y-1.5">
                        @foreach($polas as $key => $data)
                        @php $active=old('pola',$input['pola']??'Pola 1')===$key; @endphp
                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl cursor-pointer transition-colors"
                               style="border:1px solid {{ $active?'rgba(245,158,11,.3)':'rgba(255,255,255,.06)' }};background:#0d0d1a"
                               onmouseenter="this.style.borderColor='rgba(245,158,11,.2)'"
                               onmouseleave="this.style.borderColor=this.querySelector('input').checked?'rgba(245,158,11,.3)':'rgba(255,255,255,.06)'">
                            <input type="radio" name="pola" value="{{ $key }}" {{ $active?'checked':'' }}
                                   onchange="document.querySelectorAll('[name=pola]').forEach(r=>{r.closest('label').style.borderColor=r.checked?'rgba(245,158,11,.3)':'rgba(255,255,255,.06)'})"
                                   class="mt-0.5 accent-amber-500 flex-shrink-0">
                            <div>
                                <div class="text-xs font-mono text-white">{{ $key }}
                                    <span class="text-white/20 text-[10px]">[{{ implode('→',array_map(fn($s)=>str_replace(' ','',$s),$data['sections'])) }}]</span>
                                </div>
                                <p class="text-[10px] text-white/22 mt-0.5">{{ $data['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- BPM --}}
                <div>
                    <div class="flex justify-between mb-1.5">
                        <label class="text-[10px] font-mono text-white/35 uppercase tracking-widest">Tempo</label>
                        <span id="bpm-display" class="text-xs font-mono text-amber-400">{{ old('bpm',$input['bpm']??100) }} BPM</span>
                    </div>
                    <input type="range" name="bpm" min="60" max="220" step="5"
                           value="{{ old('bpm',$input['bpm']??100) }}" oninput="updateBpmDisplay(this.value)">
                    <div class="flex justify-between text-[9px] font-mono text-white/18 mt-1"><span>60 Largo</span><span>Presto 220</span></div>
                </div>

                {{-- Instruments --}}
                <div>
                    <label class="block text-[10px] font-mono text-white/35 mb-1.5 uppercase tracking-widest">Instrumen</label>
                    <div class="space-y-1">
                        @foreach($instruments as $name => $data)
                        @php $chk=!isset($input)||in_array($name,$input['instruments']??[]); @endphp
                        <label class="flex items-center gap-2 p-2 rounded-xl cursor-pointer transition-colors"
                               style="border:1px solid {{ $chk?'rgba(245,158,11,.25)':'rgba(255,255,255,.06)' }};background:#0d0d1a">
                            <input type="checkbox" name="instruments[]" value="{{ $name }}" {{ $chk?'checked':'' }}
                                   onchange="this.closest('label').style.borderColor=this.checked?'rgba(245,158,11,.25)':'rgba(255,255,255,.06)'"
                                   class="accent-amber-500 w-3.5 h-3.5 flex-shrink-0">
                            <span class="text-base leading-none">{{ $data['icon'] }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] font-mono text-white">{{ $name }}</div>
                                <div class="text-[9px] text-white/20 truncate">{{ $data['label'] }}</div>
                            </div>
                            <span class="text-[9px] font-mono text-white/14 flex-shrink-0">#{{ $data['program'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- GENERATE BUTTON — selalu visible, tidak tertutup history --}}
<div class="sticky bottom-0 bg-[#0c0c18] pt-3 pb-2">
    <button
        type="submit"
        class="btn-generate w-full py-4 rounded-xl font-display font-semibold text-sm glow-amber transition-all"
        style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#04040a">
        ⚡ Generate Chord Sequence
    </button>
</div>
            </form>
        </div>
    </div>{{-- end form-sticky --}}

    {{-- HISTORY — di luar form-sticky, mengalir normal di bawahnya --}}
    <div class="history-panel glass rounded-2xl overflow-hidden">
        <div class="px-5 py-3 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,.05)">
            <div>
                <h3 class="font-display font-semibold text-white text-sm">🕐 Riwayat</h3>
                <p class="text-white/22 text-[10px] font-mono mt-0.5">{{ $histories->count() }} sesi tersimpan</p>
            </div>
        </div>
        <div style="max-height:260px;overflow-y:auto">
            @forelse($histories as $h)
            <div class="hist-item px-4 py-2.5 flex items-center gap-2 transition-colors {{ isset($history_id)&&$history_id===$h->id?'active':'' }}"
                 style="border-bottom:1px solid rgba(255,255,255,.035)">
                <a href="{{ route('chord.history.show',$h) }}" class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <span class="font-mono text-[11px] font-semibold text-amber-400">{{ $h->genre }}</span>
                        <span class="text-white/18 text-[10px]">·</span>
                        <span class="font-mono text-[10px] text-white/45">{{ $h->pola }}</span>
                    </div>
                    <div class="text-[10px] text-white/22 font-mono truncate">{{ $h->bpm }} BPM · {{ implode(', ',$h->instruments) }}</div>
                    <div class="text-[9px] text-white/14 mt-0.5">{{ $h->created_at->diffForHumans() }}</div>
                </a>
                <button onclick="deleteHistory({{ $h->id }},this)" class="text-white/14 hover:text-rose-400 transition-colors flex-shrink-0 p-1" title="Hapus">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
                    </svg>
                </button>
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <div class="text-2xl mb-2">📭</div>
                <p class="text-white/18 text-[10px] font-mono">Belum ada riwayat</p>
            </div>
            @endforelse
        </div>
    </div>

</div>{{-- end col-sidebar --}}

{{-- ══════════════════════════════
     COL 2-4 — KONTEN UTAMA
══════════════════════════════ --}}
<div class="xl:col-span-3 space-y-4">

    {{-- TREE --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,.05)">
            <div>
                <h2 class="font-display font-semibold text-white text-sm">🌳 Struktur Binary Tree</h2>
                <p class="text-white/25 text-[10px] font-mono mt-0.5">Hierarki navigasi chord</p>
            </div>
            @if($input)
            <div class="text-[10px] font-mono px-2.5 py-1 rounded-full" style="background:rgba(34,211,238,.07);border:1px solid rgba(34,211,238,.15);color:#22d3ee">
                Root → {{ $input['genre'] }} → {{ $input['family'] }}
            </div>
            @endif
        </div>
        <div class="p-4 font-mono text-[11px]">
            {{-- Root node --}}
            <div class="flex items-center gap-2 mb-2">
                <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:#f59e0b;box-shadow:0 0 7px rgba(245,158,11,.5)"></div>
                <span class="text-amber-400 font-semibold">Sistem Generator Chord</span>
                <span class="text-white/18 text-[9px]">[root]</span>
            </div>
            @foreach($genreFamily as $genre => $families)
            @php $ag=$input&&$input['genre']===$genre; @endphp
            <div class="pl-5 border-l" style="border-color:rgba(255,255,255,.07)">
                <div class="flex items-center gap-2 py-0.5 relative">
                    <div class="absolute -left-5 w-5 h-px" style="background:rgba(255,255,255,.07)"></div>
                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $ag?'bg-cyan-400':'bg-white/12' }}" style="{{ $ag?'box-shadow:0 0 5px rgba(34,211,238,.5)':'' }}"></div>
                    <span class="{{ $ag?'text-cyan-300 font-semibold':'text-white/38' }}">{{ $genre }}</span>
                    <span class="text-white/18 text-[9px]">[genre]</span>
                </div>
                @foreach($families as $fam)
                @php
                    $af=$ag&&$input&&$input['family']===$fam;
                    $sd=['Verse'=>[],'Reff'=>[]];
                    if($fam==='C-Major Family')     $sd=['Verse'=>['C','G','Am','F'],'Reff'=>['F','G','C','Am','Dm','Em']];
                    elseif($fam==='D-Minor Family') $sd=['Verse'=>['Dm7','G7','Cmaj7'],'Reff'=>['Dm7','G7','A7','Cmaj7']];
                    elseif($fam==='G-Major Family') $sd=['Verse'=>['G_Maj','C_Maj','D_Maj'],'Reff'=>['Em_Cl','C_Maj','D_Maj','G_Maj']];
                @endphp
                <div class="pl-5 border-l" style="border-color:rgba(255,255,255,.04)">
                    <div class="flex items-center gap-2 py-0.5 relative">
                        <div class="absolute -left-5 w-5 h-px" style="background:rgba(255,255,255,.04)"></div>
                        <div class="w-2 h-2 rounded flex-shrink-0 {{ $af?'bg-violet-400':'bg-white/8' }}" style="{{ $af?'box-shadow:0 0 4px rgba(139,92,246,.5)':'' }}"></div>
                        <span class="{{ $af?'text-violet-300 font-semibold':'text-white/25' }}">{{ $fam }}</span>
                        <span class="text-white/15 text-[9px]">[family]</span>
                    </div>
                    @foreach($sd as $sec => $chords)
                    <div class="pl-5 border-l pb-1" style="border-color:rgba(255,255,255,.03)">
                        <div class="flex items-center gap-1.5 py-0.5 relative">
                            <div class="absolute -left-5 w-5 h-px" style="background:rgba(255,255,255,.03)"></div>
                            <div class="w-1.5 h-1.5 rounded flex-shrink-0 {{ $sec==='Verse'?'bg-cyan-500/35':'bg-amber-500/35' }}"></div>
                            <span class="text-[9px] {{ $sec==='Verse'?'text-cyan-500/55':'text-amber-500/55' }}">{{ $sec }}</span>
                        </div>
                        <div class="pl-3 flex flex-wrap gap-1">
                            @foreach(array_unique($chords) as $c)
                            <span class="px-1.5 py-0.5 rounded text-[9px]" style="background:rgba(255,255,255,.04);color:rgba(255,255,255,.32);border:1px solid rgba(255,255,255,.05)">{{ $c }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    {{-- EMPTY STATE --}}
    @if(!$result)
    <div class="glass rounded-2xl p-14 text-center">
        <div class="text-5xl mb-4">🎵</div>
        <h3 class="font-display font-semibold text-white/25 text-lg mb-2">Belum Ada Hasil</h3>
        <p class="text-white/18 font-body text-sm max-w-sm mx-auto">Pilih parameter lalu klik <span class="text-amber-400/45 font-mono">Generate</span>.</p>
    </div>

    @else
    {{-- ══ HASIL GENERATE ══ --}}
    <div class="space-y-4 animate-slide-up" id="hasil-section">

        {{-- ACTION BAR --}}
        <div class="glass rounded-2xl p-4 flex flex-wrap items-center gap-3">
            <button id="main-play-btn" onclick="initAndPlay()"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-display font-semibold text-sm glow-amber transition-all"
                    style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#04040a">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                Play Musik
            </button>
            <a href="{{ route('chord.download.chord', $history_id ?? 0) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-mono text-xs transition-all hover:bg-white/5"
               style="background:rgba(34,211,238,.07);border:1px solid rgba(34,211,238,.18);color:#22d3ee">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download .txt
            </a>
            <div class="ml-auto flex items-center gap-2 text-[10px] font-mono text-white/25">
                <span>{{ $input['genre'] }}</span><span class="text-white/12">→</span>
                <span>{{ $input['family'] }}</span><span class="text-white/12">→</span>
                <span>{{ $input['pola'] }}</span><span class="text-white/12">·</span>
                <span class="text-amber-400/70">{{ $input['bpm'] }} BPM</span>
            </div>
        </div>

        {{-- STATS --}}
        <div class="glass rounded-2xl p-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="font-display font-bold text-3xl text-amber-400">{{ $result['meta']['total_chords'] }}</div>
                <div class="text-[10px] font-mono text-white/25 mt-0.5">Total Antrian</div>
            </div>
            <div class="text-center">
                <div class="font-display font-bold text-3xl text-cyan-400">{{ count($result['meta']['unique_chords']) }}</div>
                <div class="text-[10px] font-mono text-white/25 mt-0.5">Chord Unik</div>
            </div>
            <div class="text-center">
                <div class="font-display font-bold text-3xl text-violet-400">{{ count($result['meta']['sections']) }}</div>
                <div class="text-[10px] font-mono text-white/25 mt-0.5">Seksi</div>
            </div>
            <div class="text-center">
                <div class="font-display font-bold text-3xl text-white/50">{{ $input['bpm'] }}</div>
                <div class="text-[10px] font-mono text-white/25 mt-0.5">BPM · 4/4</div>
            </div>
        </div>

        {{-- CHORD UNIK + INSTRUMEN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="glass rounded-2xl p-4">
                <p class="text-[9px] font-mono text-white/30 uppercase tracking-widest mb-2.5">Chord Digunakan</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($result['meta']['unique_chords'] as $uc)
                    <div class="px-2.5 py-1.5 rounded-lg font-mono text-xs text-white"
                         style="background:rgba(245,158,11,.09);border:1px solid rgba(245,158,11,.18)">{{ $uc }}</div>
                    @endforeach
                </div>
            </div>
            <div class="glass rounded-2xl p-4">
                <p class="text-[9px] font-mono text-white/30 uppercase tracking-widest mb-2.5">Instrumen Aktif</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($input['instruments'] as $inst)
                    <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg"
                         style="background:rgba(34,211,238,.07);border:1px solid rgba(34,211,238,.14)">
                        <span class="text-sm leading-none">{{ $instruments[$inst]['icon'] }}</span>
                        <span class="font-mono text-[11px] text-cyan-300">{{ $inst }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- QUEUE BY SECTION --}}
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,.05)">
                <div>
                    <h2 class="font-display font-semibold text-white text-sm">📋 Antrean Playback (FIFO Queue)</h2>
                    <p class="text-white/22 text-[10px] font-mono mt-0.5">Klik kartu chord untuk loncat ke posisi tersebut</p>
                </div>
                <span class="text-[10px] font-mono text-white/22">{{ $result['meta']['total_chords'] }} items</span>
            </div>
            <div class="divide-y" style="divide-color:rgba(255,255,255,.03)">
                @php $ci=0; @endphp
                @foreach($result['meta']['section_groups'] as $grp)
                @php
                    $isV=str_starts_with(strtolower($grp['base']),'verse');
                    $col =$isV?'#22d3ee':'#f59e0b';
                    $bgc =$isV?'rgba(34,211,238,.015)':'rgba(245,158,11,.015)';
                    $bdg =$isV?'rgba(34,211,238,.1)':'rgba(245,158,11,.1)';
                    $hdr =$isV?'rgba(34,211,238,.03)':'rgba(245,158,11,.03)';
                @endphp
                <div class="chord-item" style="border-left:3px solid {{ $col }};background:{{ $bgc }}">
                    <div class="px-4 py-2 flex items-center gap-2"
                         style="background:{{ $hdr }};border-bottom:1px solid rgba(255,255,255,.03)">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold"
                              style="background:{{ $bdg }};color:{{ $col }}">{{ $grp['label'] }}</span>
                        <span class="text-[10px] text-white/18 font-mono">{{ count($grp['chords']) }} chord</span>
                    </div>
                    <div class="p-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2">
                        @foreach($grp['chords'] as $item)
                        <div class="chord-card rounded-xl p-2.5 cursor-pointer transition-all hover:scale-[1.03]"
                             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)"
                             onclick="jumpToChord({{ $ci }})">
                            <div class="text-[9px] font-mono text-white/18 mb-1">{{ $item['nomor'] }}</div>
                            <div class="font-display font-bold text-base leading-none" style="color:{{ $col }}">{{ $item['akor'] }}</div>
                            <div class="flex flex-wrap gap-0.5 mt-1.5">
                                @foreach($item['not'] as $note)
                                <span class="piano-key {{ str_contains($note,'#')?'black':'white' }}">{{ $note }}</span>
                                @endforeach
                            </div>
                        </div>
                        @php $ci++; @endphp
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- LOG TABLE --}}
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-3.5" style="border-bottom:1px solid rgba(255,255,255,.05)">
                <h2 class="font-display font-semibold text-white text-sm">📊 Log Urutan Playback Lengkap</h2>
                <p class="text-white/22 text-[10px] font-mono mt-0.5">Setara output terminal Python Colab</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[11px] font-mono">
                    <thead>
                        <tr style="background:rgba(255,255,255,.025);border-bottom:1px solid rgba(255,255,255,.05)">
                            <th class="px-4 py-2 text-left text-white/30 font-normal">#</th>
                            <th class="px-4 py-2 text-left text-white/30 font-normal">Seksi</th>
                            <th class="px-4 py-2 text-left text-white/30 font-normal">Akor</th>
                            <th class="px-4 py-2 text-left text-white/30 font-normal">Susunan Not</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['queue'] as $item)
                        @php $iv=str_starts_with(strtolower($item['seksi_base']),'verse'); @endphp
                        <tr style="border-bottom:1px solid rgba(255,255,255,.025)" class="hover:bg-white/[.015] transition-colors">
                            <td class="px-4 py-1.5 text-white/18">{{ $item['nomor'] }}</td>
                            <td class="px-4 py-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px]"
                                      style="background:{{ $iv?'rgba(34,211,238,.07)':'rgba(245,158,11,.07)' }};color:{{ $iv?'#22d3ee':'#f59e0b' }}">{{ $item['seksi'] }}</span>
                            </td>
                            <td class="px-4 py-1.5 font-semibold text-white/85">{{ $item['akor'] }}</td>
                            <td class="px-4 py-1.5 text-white/30">[{{ implode(', ',$item['not']) }}]</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- end hasil-section --}}
    @endif

</div>{{-- end col 2-4 --}}
</div>{{-- end grid --}}
</div>{{-- end container --}}

@endsection

@push('scripts')
<script>
@if($result)
// Data dari PHP — diinject sekali
const PHP_QUEUE = @json($result['queue']);
const PHP_BPM   = {{ intval($input['bpm'] ?? 100) }};
const PHP_INSTS = @json($input['instruments']);

// ── initAndPlay — tombol Play di halaman ──────────────────────────
async function initAndPlay(){
    const btn=document.getElementById('main-play-btn');
    btn.disabled=true;
    btn.innerHTML=`<svg class="spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Memuat…`;

    await Tone.start();
    loadData(PHP_QUEUE, PHP_BPM, PHP_INSTS);

    // Jika sedang play → pause; jika tidak → play
    if(PS_.playing){
        pause_();
    } else {
        await play_();
    }

    btn.disabled=false;
    _syncMainBtn();
}

// ── jumpToChord — klik kartu chord ────────────────────────────────
async function jumpToChord(idx){
    if(!PS_.queue.length) loadData(PHP_QUEUE,PHP_BPM,PHP_INSTS);
    const was=PS_.playing;
    Tone.Transport.cancel(); Tone.Transport.stop(); PS_.playing=false;
    PS_.cur=idx;
    updateUI(idx);
    if(was) await play_();
}

// Sync ikon tombol play utama dengan state player
function _syncMainBtn(){
    const btn=document.getElementById('main-play-btn');
    if(!btn)return;
    btn.innerHTML=PS_.playing
        ?`<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg> Pause`
        :`<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg> Play Musik`;
}

// Override setBtn agar juga sync tombol utama
const _origSetBtn=setBtn;
window.setBtn=function(p){ _origSetBtn(p); _syncMainBtn(); };

// Pre-load data saat halaman siap (sampel di-load di background)
window.addEventListener('load',()=>{
    loadData(PHP_QUEUE,PHP_BPM,PHP_INSTS);
    // Pre-load sampel di background (tanpa auto-play)
    Tone.start().then(()=>ensureReady(PHP_INSTS)).catch(()=>{});
});
@endif

// ── Hapus history via AJAX ────────────────────────────────────────
function deleteHistory(id,btn){
    if(!confirm('Hapus riwayat ini?'))return;
    fetch(`/history/${id}`,{
        method:'DELETE',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
    }).then(r=>r.json()).then(d=>{
        if(d.status==='deleted') btn.closest('.hist-item').remove();
    });
}

// Scroll ke hasil setelah generate
@if($result)
setTimeout(()=>document.getElementById('hasil-section')?.scrollIntoView({behavior:'smooth',block:'start'}),350);
@endif
</script>
@endpush
