<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chord_histories', function (Blueprint $table) {
            $table->id();
            $table->string('genre');
            $table->string('family');
            $table->string('pola');
            $table->integer('bpm');
            $table->json('instruments');
            $table->json('result_data');   // full queue + meta JSON
            $table->string('session_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chord_histories');
    }
};
