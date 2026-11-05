<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_infill', function (Blueprint $table) {
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('infill_id')->constrained()->cascadeOnDelete();
            $table->unique(['component_id', 'infill_id']);
            $table->index('infill_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_infill');
    }
};
