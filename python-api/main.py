"""
==========================================================
  CHORD MUSIC GENERATOR — FastAPI Backend
  Jalankan: uvicorn main:app --host 0.0.0.0 --port 8000 --reload
  Docs:     http://localhost:8000/docs
==========================================================
"""

import random
import os
import ctypes
import platform
import shutil
import subprocess
import pretty_midi
import fluidsynth
from collections import deque
from datetime import datetime
from pydub import AudioSegment
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import FileResponse
from pydantic import BaseModel
from typing import List, Optional

# Load FluidSynth DLL dari folder yang sama dengan main.py
_here = os.path.dirname(os.path.abspath(__file__))
os.add_dll_directory(_here)
_dll = os.path.join(_here, "libfluidsynth-3.dll")
if os.path.exists(_dll):
    ctypes.cdll.LoadLibrary(_dll)

# Paksa load DLL dari folder lokal
dll_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "libfluidsynth-3.dll")
if os.path.exists(dll_path):
    import ctypes
    ctypes.cdll.LoadLibrary(dll_path)

# ==========================================
# HELPER: DETEKSI FLUIDSYNTH & SOUNDFONT
# ==========================================
def find_fluidsynth() -> Optional[str]:
    """Cari executable fluidsynth di PATH atau lokasi umum."""
    # Cek di PATH dulu (berlaku di semua OS termasuk venv di Linux/Mac/WSL)
    found = shutil.which("fluidsynth")
    if found:
        return found

    # Fallback lokasi Windows jika tidak ada di PATH
    windows_paths = [
        r"C:\tools\fluidsynth\bin\fluidsynth.exe",
        r"C:\Program Files\FluidSynth\bin\fluidsynth.exe",
        r"C:\Program Files (x86)\FluidSynth\bin\fluidsynth.exe",
    ]
    for p in windows_paths:
        if os.path.exists(p):
            return p

    return None


def find_soundfont() -> Optional[str]:
    """Cari file soundfont .sf2 secara otomatis sesuai OS."""
    system = platform.system()

    if system == "Linux":
        candidates = [
            "/usr/share/sounds/sf2/FluidR3_GM.sf2",
            "/usr/share/sounds/sf2/FluidR3_GM2.sf2",
            "/usr/share/soundfonts/FluidR3_GM.sf2",
            "/usr/share/sounds/sf2/default.sf2",
            "/usr/share/soundfonts/default.sf2",
        ]
    elif system == "Darwin":  # macOS
        candidates = [
            "/usr/local/share/sounds/sf2/FluidR3_GM.sf2",
            "/opt/homebrew/share/sounds/sf2/FluidR3_GM.sf2",
            os.path.expanduser("~/soundfonts/FluidR3_GM.sf2"),
        ]
    elif system == "Windows":
        candidates = [
            r"C:\tools\fluidsynth\soundfonts\FluidR3_GM.sf2",
            r"C:\Program Files\FluidSynth\soundfonts\FluidR3_GM.sf2",
            r"C:\soundfonts\FluidR3_GM.sf2",
            os.path.join(os.environ.get("USERPROFILE", ""), "soundfonts", "FluidR3_GM.sf2"),
        ]
    else:
        candidates = []

    # Cek semua kandidat
    for path in candidates:
        if os.path.exists(path):
            return path

    # Fallback: cari .sf2 di direktori umum lainnya (berlaku semua OS)
    extra = [
        os.path.expanduser("~/soundfonts/FluidR3_GM.sf2"),
        os.path.expanduser("~/.local/share/sounds/sf2/FluidR3_GM.sf2"),
        "/soundfonts/FluidR3_GM.sf2",
        # Tambahkan ini di dalam find_soundfont(), di list extra:
os.path.join(os.path.dirname(__file__), "soundfonts", "GeneralUser_GS.sf2"),
os.path.join(os.path.dirname(__file__), "soundfonts", "FluidR3_GM.sf2"),
    ]
    for path in extra:
        if os.path.exists(path):
            return path

    return None

# ==========================================
# APP INIT
# ==========================================
app = FastAPI(
    title="Chord Music Generator API",
    description="API untuk menghasilkan progres chord dan file audio MP3",
    version="1.0.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],        # Ganti dengan domain Laravel kamu di production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

OUTPUT_FOLDER = "kumpulan_lagu"
os.makedirs(OUTPUT_FOLDER, exist_ok=True)

# ==========================================
# STRUKTUR DATA: NODE TREE
# ==========================================
class TreeNode:
    def __init__(self, name):
        self.name = name
        self.children = {}

    def add_child(self, name, node):
        self.children[name] = node

# ==========================================
# KAMUS CHORD & NOTASI MIDI
# ==========================================
NOTE_MIDI = {
    'C2': 36, 'D2': 38, 'E2': 40, 'F2': 41, 'G2': 43, 'A2': 45, 'B2': 47,
    'C3': 48, 'D3': 50, 'E3': 52, 'F3': 53, 'F#3': 54, 'G3': 55,
    'A3': 57, 'A#3': 58, 'B3': 59,
    'C4': 60, 'C#4': 61, 'D4': 62, 'E4': 64, 'F4': 65, 'G4': 67,
    'A4': 69, 'B4': 71
}

CHORD_NOTES_MAP = {
    'C':     ['C4', 'E4', 'G4'],
    'G':     ['G3', 'B3', 'D4'],
    'Am':    ['A3', 'C4', 'E4'],
    'F':     ['F3', 'A3', 'C4'],
    'Dm':    ['D3', 'F3', 'A3'],
    'Em':    ['E3', 'G3', 'B3'],
    'Dm7':   ['D3', 'F3', 'A3', 'C4'],
    'G7':    ['G3', 'B3', 'D4', 'F4'],
    'Cmaj7': ['C4', 'E4', 'G4', 'B4'],
    'A7':    ['A3', 'C#4', 'E4', 'G4'],
    'G_Maj': ['G3', 'B3', 'D4'],
    'C_Maj': ['C4', 'E4', 'G4'],
    'D_Maj': ['D3', 'F#3', 'A3'],
    'Em_Cl': ['E3', 'G3', 'B3'],
}

INSTRUMENT_PROGRAMS = {
    "Piano":     0,
    "Guitar":    24,
    "Bass":      32,
    "Strings":   48,
    "Synth Pad": 89,
}

# ==========================================
# BANGUN TREE
# ==========================================
root = TreeNode("Sistem Generator Chord")

pop_node     = TreeNode("Pop")
jazz_node    = TreeNode("Jazz")
classic_node = TreeNode("Classic")
root.add_child("Pop",     pop_node)
root.add_child("Jazz",    jazz_node)
root.add_child("Classic", classic_node)

c_major_family = TreeNode("C-Major Family")
d_minor_family = TreeNode("D-Minor Family")
g_major_family = TreeNode("G-Major Family")
pop_node.add_child("C-Major Family",  c_major_family)
jazz_node.add_child("D-Minor Family", d_minor_family)
classic_node.add_child("G-Major Family", g_major_family)

c_major_family.add_child("Verse", ["C", "G", "Am", "F"])
c_major_family.add_child("Reff",  ["F", "G", "C", "Am", "Dm", "Em"])
d_minor_family.add_child("Verse", ["Dm7", "G7", "Cmaj7"])
d_minor_family.add_child("Reff",  ["Dm7", "G7", "A7", "Cmaj7"])
g_major_family.add_child("Verse", ["G_Maj", "C_Maj", "G_Maj", "D_Maj"])
g_major_family.add_child("Reff",  ["Em_Cl", "C_Maj", "D_Maj", "G_Maj"])

GENRE_FAMILY_MAP = {
    "Pop":     ["C-Major Family"],
    "Jazz":    ["D-Minor Family"],
    "Classic": ["G-Major Family"],
}

# ==========================================
# PYDANTIC MODELS
# ==========================================
class GenerateRequest(BaseModel):
    genre:       str = "Pop"
    family:      str = "C-Major Family"
    pola:        str = "Pola 1"
    instruments: List[str] = ["Piano", "Bass", "Guitar"]
    bpm:         int = 100

class ChordItem(BaseModel):
    seksi: str
    akor:  str
    notes: List[str]

class GenerateResponse(BaseModel):
    status:   str
    sequence: List[ChordItem]
    mp3_url:  str
    filename: str
    total_chords: int

# ==========================================
# ENGINE CORE
# ==========================================
def generate_chord_sequence(genre: str, family: str, pola_pilihan: str):
    master_pola = {
        "Pola 1": ["Verse 1", "Reff 1", "Verse 2", "Reff 2"],
        "Pola 2": ["Verse 1", "Reff",   "Verse 2"],
        "Pola 3": ["Verse",   "Reff 1", "Reff 2"],
    }

    if pola_pilihan not in master_pola:
        return None, f"Pola '{pola_pilihan}' tidak ditemukan!"

    try:
        family_node = root.children[genre].children[family]
        verse_pool  = family_node.children["Verse"]
        reff_pool   = family_node.children["Reff"]
    except KeyError:
        return None, f"Genre '{genre}' atau Family '{family}' tidak ditemukan!"

    paket_chords = {
        "Verse":   random.sample(verse_pool, len(verse_pool)),
        "Verse 1": random.sample(verse_pool, len(verse_pool)),
        "Verse 2": random.sample(verse_pool, len(verse_pool)),
        "Reff":    random.sample(reff_pool,  len(reff_pool)),
        "Reff 1":  random.sample(reff_pool,  len(reff_pool)),
        "Reff 2":  random.sample(reff_pool,  len(reff_pool)),
    }

    song_queue = deque()
    for seksi in master_pola[pola_pilihan]:
        for rep in range(2):
            for chord in paket_chords[seksi]:
                song_queue.append({
                    "seksi": f"{seksi} (Rep {rep+1})",
                    "akor":  chord,
                    "notes": CHORD_NOTES_MAP.get(chord, []),
                })

    return song_queue, "Sukses"


def render_audio(queue_data, instruments: list, bpm: int) -> Optional[str]:
    import numpy as np
    import fluidsynth

    pm = pretty_midi.PrettyMIDI(initial_tempo=bpm)
    duration_per_beat = 60.0 / bpm

    midi_instruments = {}
    for inst_name in instruments:
        program_id = INSTRUMENT_PROGRAMS.get(inst_name, 0)
        midi_instruments[inst_name] = pretty_midi.Instrument(
            program=program_id, name=inst_name
        )

    current_time = 0.0
    for item in queue_data:
        notes = item["notes"]
        if notes:
            for beat in range(4):
                beat_start = current_time + (beat * duration_per_beat)
                beat_end   = beat_start + duration_per_beat - 0.05

                for inst_name, midi_inst in midi_instruments.items():
                    if inst_name == "Bass":
                        root_note = notes[0].replace('4', '2').replace('3', '2')
                        midi_code = NOTE_MIDI.get(root_note, 36)
                        midi_inst.notes.append(
                            pretty_midi.Note(velocity=80, pitch=midi_code,
                                             start=beat_start, end=beat_end)
                        )
                    else:
                        for note_name in notes:
                            n = note_name.replace('4', '3') if inst_name == "Guitar" else note_name
                            midi_code = NOTE_MIDI.get(n, 60)
                            velocity  = 90 if inst_name == "Piano" else 70
                            midi_inst.notes.append(
                                pretty_midi.Note(velocity=velocity, pitch=midi_code,
                                                 start=beat_start, end=beat_end)
                            )
        current_time += (4 * duration_per_beat)

    for midi_inst in midi_instruments.values():
        pm.instruments.append(midi_inst)

    timestamp      = datetime.now().strftime("%Y%m%d_%H%M%S")
    inst_tag       = "_".join(instruments).replace(" ", "")
    temp_midi      = os.path.join(OUTPUT_FOLDER, f"temp_{timestamp}.mid")
    final_mp3      = os.path.join(OUTPUT_FOLDER, f"lagu_{inst_tag}_{timestamp}.mp3")
    final_filename = os.path.basename(final_mp3)

    pm.write(temp_midi)

    # Cari soundfont
    soundfont = find_soundfont()
    if not soundfont:
        print("[ERROR] Soundfont tidak ditemukan.")
        print("[INFO]  Buat folder 'soundfonts' di dalam python-api/, lalu download:")
        print("[INFO]  https://github.com/mrbumpy409/GeneralUser-GS/raw/master/GeneralUser%20GS%20v1.471.sf2")
        print("[INFO]  Simpan sebagai: python-api/soundfonts/GeneralUser_GS.sf2")
        if os.path.exists(temp_midi):
            os.remove(temp_midi)
        return None

    print(f"[INFO] Soundfont: {soundfont}")

    try:
        # Render MIDI ke audio menggunakan pyfluidsynth langsung
        audio_data = pm.fluidsynth(fs=44100, sf2_path=soundfont)

        # Normalisasi ke int16
        audio_data = np.int16(audio_data / np.max(np.abs(audio_data)) * 32767)

        # Simpan sebagai WAV sementara lalu convert ke MP3
        temp_wav = os.path.join(OUTPUT_FOLDER, f"temp_{timestamp}.wav")
        import scipy.io.wavfile as wav
        wav.write(temp_wav, 44100, audio_data)

        sound = AudioSegment.from_wav(temp_wav)
        sound.export(final_mp3, format="mp3", bitrate="192k")
        os.remove(temp_wav)
        print(f"[INFO] MP3 berhasil dibuat: {final_filename}")
        return final_filename

    except Exception as e:
        print(f"[ERROR] Gagal render audio: {e}")
        return None
    finally:
        if os.path.exists(temp_midi):
            os.remove(temp_midi)
# ==========================================
# ROUTES
# ==========================================
@app.get("/")
def root_info():
    return {
        "app":     "Chord Music Generator API",
        "version": "1.0.0",
        "docs":    "/docs",
    }


@app.get("/options")
def get_options():
    """Kembalikan semua opsi genre, family, pola, dan instrumen yang tersedia."""
    return {
        "genres":      list(root.children.keys()),
        "families":    GENRE_FAMILY_MAP,
        "polas":       ["Pola 1", "Pola 2", "Pola 3"],
        "instruments": list(INSTRUMENT_PROGRAMS.keys()),
        "bpm_range":   {"min": 60, "max": 200, "default": 100},
    }


@app.post("/generate", response_model=GenerateResponse)
def generate_music(req: GenerateRequest):
    """Generate progres chord dan render ke file MP3."""

    # Validasi instrumen
    invalid = [i for i in req.instruments if i not in INSTRUMENT_PROGRAMS]
    if invalid:
        raise HTTPException(400, detail=f"Instrumen tidak dikenal: {invalid}")

    if not (60 <= req.bpm <= 200):
        raise HTTPException(400, detail="BPM harus antara 60 dan 200")

    # Generate sequence
    queue, status = generate_chord_sequence(req.genre, req.family, req.pola)
    if queue is None:
        raise HTTPException(400, detail=status)

    sequence_list = list(queue)

    # Render audio
    filename = render_audio(sequence_list, req.instruments, req.bpm)
    if filename is None:
        raise HTTPException(500, detail="Gagal merender audio. Pastikan FluidSynth terinstal.")

    return GenerateResponse(
        status="ok",
        sequence=[ChordItem(seksi=i["seksi"], akor=i["akor"], notes=i["notes"])
                  for i in sequence_list],
        mp3_url=f"/audio/{filename}",
        filename=filename,
        total_chords=len(sequence_list),
    )


@app.get("/audio/{filename}")
def serve_audio(filename: str):
    """Serve file MP3 yang sudah digenerate."""
    # Cegah path traversal
    if ".." in filename or "/" in filename:
        raise HTTPException(400, detail="Nama file tidak valid")

    filepath = os.path.join(OUTPUT_FOLDER, filename)
    if not os.path.exists(filepath):
        raise HTTPException(404, detail="File tidak ditemukan")

    return FileResponse(filepath, media_type="audio/mpeg",
                        filename=filename)


@app.delete("/audio/{filename}")
def delete_audio(filename: str):
    """Hapus file MP3 (opsional, untuk cleanup)."""
    if ".." in filename or "/" in filename:
        raise HTTPException(400, detail="Nama file tidak valid")

    filepath = os.path.join(OUTPUT_FOLDER, filename)
    if not os.path.exists(filepath):
        raise HTTPException(404, detail="File tidak ditemukan")

    os.remove(filepath)
    return {"status": "deleted", "filename": filename}
