<?php

namespace App\Http\Controllers;

use App\Models\ChordHistory;
use App\Services\ChordTree;
use App\Services\ChordQueueGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ChordController extends Controller
{
    private ChordTree $tree;
    private ChordQueueGenerator $generator;

    public function __construct()
    {
        $this->tree      = new ChordTree();
        $this->generator = new ChordQueueGenerator($this->tree);
    }

    // ─── GET / ────────────────────────────────────────────────────────────────
    public function index()
    {
        $histories = ChordHistory::orderByDesc('created_at')->limit(20)->get();

        return view('chord.index', [
            'genreFamily' => $this->tree->genreFamilyMap,
            'polas'       => $this->tree->polaDescriptions,
            'instruments' => $this->tree->instrumentPrograms,
            'result'      => null,
            'input'       => null,
            'histories'   => $histories,
        ]);
    }

    // ─── POST /generate ───────────────────────────────────────────────────────
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'genre'           => 'required|string|in:Pop,Jazz,Classic',
            'family'          => 'required|string',
            'pola'            => 'required|string|in:Pola 1,Pola 2,Pola 3',
            'bpm'             => 'required|integer|min:60|max:220',
            'instruments'     => 'required|array|min:1',
            'instruments.*'   => 'string|in:Piano,Guitar,Bass,Strings,Synth Pad',
        ]);

        $availableFamilies = $this->tree->genreFamilyMap[$validated['genre']] ?? [];
        if (!in_array($validated['family'], $availableFamilies)) {
            return back()->withInput()->withErrors(['family' => 'Family tidak tersedia untuk genre tersebut.']);
        }

        $result = $this->generator->generate($validated['genre'], $validated['family'], $validated['pola']);

        if ($result['status'] !== 'success') {
            return back()->withInput()->withErrors(['error' => $result['message']]);
        }

        // Simpan ke history
        $history = ChordHistory::create([
            'genre'       => $validated['genre'],
            'family'      => $validated['family'],
            'pola'        => $validated['pola'],
            'bpm'         => $validated['bpm'],
            'instruments' => $validated['instruments'],
            'result_data' => array_merge($result, ['input' => $validated]),
            'session_id'  => session()->getId(),
        ]);

        $histories = ChordHistory::orderByDesc('created_at')->limit(20)->get();

        return view('chord.index', [
            'genreFamily'  => $this->tree->genreFamilyMap,
            'polas'        => $this->tree->polaDescriptions,
            'instruments'  => $this->tree->instrumentPrograms,
            'result'       => $result,
            'input'        => $validated,
            'histories'    => $histories,
            'history_id'   => $history->id,
        ]);
    }

    // ─── GET /api/families ────────────────────────────────────────────────────
    public function getFamiliesByGenre(Request $request): JsonResponse
    {
        $genre    = $request->input('genre');
        $families = $this->tree->genreFamilyMap[$genre] ?? [];
        return response()->json(['families' => $families]);
    }

    // ─── GET /history/{id} ────────────────────────────────────────────────────
    public function showHistory(ChordHistory $history)
    {
        $data    = $history->result_data;
        $result  = ['queue' => $data['queue'] ?? [], 'status' => 'success', 'message' => 'Sukses', 'meta' => $data['meta'] ?? []];
        $input   = $data['input'] ?? [
            'genre'       => $history->genre,
            'family'      => $history->family,
            'pola'        => $history->pola,
            'bpm'         => $history->bpm,
            'instruments' => $history->instruments,
        ];

        $histories = ChordHistory::orderByDesc('created_at')->limit(20)->get();

        return view('chord.index', [
            'genreFamily' => $this->tree->genreFamilyMap,
            'polas'       => $this->tree->polaDescriptions,
            'instruments' => $this->tree->instrumentPrograms,
            'result'      => $result,
            'input'       => $input,
            'histories'   => $histories,
            'history_id'  => $history->id,
        ]);
    }

    // ─── DELETE /history/{id} ─────────────────────────────────────────────────
    public function deleteHistory(ChordHistory $history): JsonResponse
    {
        $history->delete();
        return response()->json(['status' => 'deleted']);
    }

    // ─── GET /download/chord/{id} ─────────────────────────────────────────────
    public function downloadChord(ChordHistory $history)
    {
        $data   = $history->result_data;
        $queue  = $data['queue'] ?? [];
        $meta   = $data['meta'] ?? [];
        $input  = $data['input'] ?? [];

        $lines   = [];
        $lines[] = "=== CHORDGEN — HASIL GENERATE CHORD PROGRESSION ===";
        $lines[] = str_repeat("=", 55);
        $lines[] = "Tanggal  : " . $history->created_at->format('d/m/Y H:i:s');
        $lines[] = "Genre    : " . $history->genre;
        $lines[] = "Family   : " . $history->family;
        $lines[] = "Pola     : " . $history->pola;
        $lines[] = "BPM      : " . $history->bpm;
        $lines[] = "Instrumen: " . implode(', ', $history->instruments);
        $lines[] = "";
        $lines[] = "--- RINGKASAN ---";
        $lines[] = "Total Akor     : " . ($meta['total_chords'] ?? 0);
        $lines[] = "Chord Unik     : " . implode(', ', $meta['unique_chords'] ?? []);
        $lines[] = "Seksi          : " . implode(' → ', $meta['sections'] ?? []);
        $lines[] = "";
        $lines[] = str_repeat("-", 70);
        $lines[] = sprintf("%-4s %-22s %-10s %s", "#", "Seksi", "Akor", "Susunan Not");
        $lines[] = str_repeat("-", 70);

        foreach ($queue as $item) {
            $notes = is_array($item['not']) ? implode(', ', $item['not']) : '';
            $lines[] = sprintf("%-4s %-22s %-10s [%s]",
                $item['nomor'],
                $item['seksi'],
                $item['akor'],
                $notes
            );
        }

        $lines[] = str_repeat("=", 70);
        $lines[] = "Generated by ChordGen — Laravel + Binary Tree + FIFO Queue";

        $content  = implode("\n", $lines);
        $filename = "chordgen_{$history->genre}_{$history->pola}_{$history->id}.txt";

        return response($content, 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── GET /api/history/{id}/data (untuk reload result via JS) ──────────────
    public function historyData(ChordHistory $history): JsonResponse
    {
        $data  = $history->result_data;
        $input = $data['input'] ?? [
            'genre'       => $history->genre,
            'family'      => $history->family,
            'pola'        => $history->pola,
            'bpm'         => $history->bpm,
            'instruments' => $history->instruments,
        ];

        return response()->json([
            'result'     => ['queue' => $data['queue'] ?? [], 'status' => 'success', 'meta' => $data['meta'] ?? []],
            'input'      => $input,
            'history_id' => $history->id,
        ]);
    }
}
