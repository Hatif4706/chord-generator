<?php

namespace App\Http\Controllers;

use App\Models\ChordHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MusicGeneratorController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = config('services.music_api.url', 'http://localhost:8000');
    }

    // ------------------------------------------------
    // Cek status Python API
    // ------------------------------------------------
    public function apiStatus()
    {
        try {
            $response = Http::timeout(3)->get("{$this->apiBase}/");
            return response()->json(['online' => $response->successful()]);
        } catch (\Exception $e) {
            return response()->json(['online' => false]);
        }
    }

    // ------------------------------------------------
    // Halaman utama
    // ------------------------------------------------
    public function index()
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiBase}/options");
            $options  = $response->successful() ? $response->json() : $this->defaultOptions();
        } catch (\Exception $e) {
            $options = $this->defaultOptions();
        }

        $histories = ChordHistory::orderByDesc('created_at')->limit(15)->get();

        return view('music.generator', compact('options', 'histories'));
    }

    // ------------------------------------------------
    // Generate musik — simpan ke history
    // ------------------------------------------------
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'genre'         => 'required|string',
            'family'        => 'required|string',
            'pola'          => 'required|string',
            'instruments'   => 'required|array|min:1',
            'instruments.*' => 'string',
            'bpm'           => 'required|integer|min:60|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $response = Http::timeout(120)->post("{$this->apiBase}/generate", [
                'genre'       => $request->genre,
                'family'      => $request->family,
                'pola'        => $request->pola,
                'instruments' => $request->instruments,
                'bpm'         => (int) $request->bpm,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Proxy audio melalui Laravel
                $data['mp3_url'] = route('music.audio', ['filename' => $data['filename']]);

                // ── Simpan ke chord_histories ──────────────────────────────
                $history = ChordHistory::create([
                    'genre'       => $request->genre,
                    'family'      => $request->family,
                    'pola'        => $request->pola,
                    'bpm'         => (int) $request->bpm,
                    'instruments' => $request->instruments,
                    'session_id'  => session()->getId(),
                    'result_data' => [
                        'filename'     => $data['filename'],
                        'total_chords' => $data['total_chords'] ?? 0,
                        'sequence'     => $data['sequence'] ?? [],
                        'meta'         => [
                            'genre'       => $request->genre,
                            'family'      => $request->family,
                            'pola'        => $request->pola,
                            'bpm'         => (int) $request->bpm,
                            'instruments' => $request->instruments,
                        ],
                    ],
                ]);

                $data['history_id'] = $history->id;

                return response()->json(['success' => true, 'data' => $data]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json('detail') ?? 'Gagal menghubungi Python API',
            ], $response->status());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke Python API. Pastikan server berjalan di ' . $this->apiBase,
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ------------------------------------------------
    // Serve audio — fix: tambahkan ?dl=1 untuk download
    // ------------------------------------------------
    public function serveAudio(Request $request, string $filename)
    {
        if (str_contains($filename, '..') || str_contains($filename, '/')) {
            abort(400, 'Nama file tidak valid');
        }

        $isDownload = $request->query('dl') === '1';

        try {
            $response = Http::timeout(30)->get("{$this->apiBase}/audio/{$filename}");

            if ($response->successful()) {
                $disposition = $isDownload
                    ? "attachment; filename=\"{$filename}\""
                    : "inline; filename=\"{$filename}\"";

                return response($response->body(), 200, [
                    'Content-Type'        => 'audio/mpeg',
                    'Content-Disposition' => $disposition,
                    'Cache-Control'       => 'no-store',
                ]);
            }

            abort(404, 'File audio tidak ditemukan');
        } catch (\Exception $e) {
            abort(503, 'Tidak dapat mengambil file audio dari Python API');
        }
    }

    // ------------------------------------------------
    // Ambil list history (JSON, dipanggil dari JS)
    // ------------------------------------------------
    public function historyList()
    {
        $histories = ChordHistory::orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn($h) => [
                'id'         => $h->id,
                'genre'      => $h->genre,
                'family'     => $h->family,
                'pola'       => $h->pola,
                'bpm'        => $h->bpm,
                'instruments'=> $h->instruments,
                'created_at' => $h->created_at->diffForHumans(),
                'filename'   => $h->result_data['filename'] ?? null,
                'sequence'   => $h->result_data['sequence'] ?? [],
                'total_chords' => $h->result_data['total_chords'] ?? 0,
            ]);

        return response()->json(['histories' => $histories]);
    }

    // ------------------------------------------------
    // Hapus satu history
    // ------------------------------------------------
    public function deleteHistory(ChordHistory $history)
    {
        $history->delete();
        return response()->json(['status' => 'deleted']);
    }

    // ------------------------------------------------
    // Default options
    // ------------------------------------------------
    private function defaultOptions(): array
    {
        return [
            'genres'      => ['Pop', 'Jazz', 'Classic'],
            'families'    => [
                'Pop'     => ['C-Major Family'],
                'Jazz'    => ['D-Minor Family'],
                'Classic' => ['G-Major Family'],
            ],
            'polas'       => ['Pola 1', 'Pola 2', 'Pola 3'],
            'instruments' => ['Piano', 'Guitar', 'Bass', 'Strings', 'Synth Pad'],
            'bpm_range'   => ['min' => 60, 'max' => 200, 'default' => 100],
        ];
    }
}
