<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->date('match_date');
            $table->timestamps();

            $table->unique(['season_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
