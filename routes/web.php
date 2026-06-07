<?php

use App\Http\Controllers\ChordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MusicGeneratorController;

// Redirect root ke halaman music generator
Route::get('/', function () {
    return redirect()->route('music.index');
});

Route::prefix('music')->name('music.')->group(function () {
    Route::get('/',                         [MusicGeneratorController::class, 'index'])        ->name('index');
    Route::get('/api-status',               [MusicGeneratorController::class, 'apiStatus'])    ->name('api-status');
    Route::post('/generate',                [MusicGeneratorController::class, 'generate'])     ->name('generate');
    Route::get('/audio/{filename}',         [MusicGeneratorController::class, 'serveAudio'])   ->name('audio');
    Route::get('/history',                  [MusicGeneratorController::class, 'historyList'])  ->name('history.list');
    Route::delete('/history/{history}',     [MusicGeneratorController::class, 'deleteHistory'])->name('history.delete');
});

// History routes (lama, dari ChordController — tetap dipertahankan)
Route::get('/history/{history}',            [ChordController::class, 'showHistory'])->name('chord.history.show');
Route::delete('/history/{history}',         [ChordController::class, 'deleteHistory'])->name('chord.history.delete');
Route::get('/api/history/{history}/data',   [ChordController::class, 'historyData'])->name('chord.history.data');

// Download routes
Route::get('/download/chord/{history}',     [ChordController::class, 'downloadChord'])->name('chord.download.chord');
