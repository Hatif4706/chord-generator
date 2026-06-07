<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChordHistory extends Model
{
    protected $fillable = [
        'genre', 'family', 'pola', 'bpm', 'instruments', 'result_data', 'session_id',
    ];

    protected $casts = [
        'instruments' => 'array',
        'result_data' => 'array',
    ];

    public function getPolaLabelAttribute(): string
    {
        return $this->pola;
    }

    public function getInstrumentListAttribute(): string
    {
        return implode(', ', $this->instruments ?? []);
    }
}
