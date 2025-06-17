<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monster_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained()->onDelete('cascade');
            $table->foreignId('encounter_id')->constrained()->onDelete('cascade');
            $table->integer('current_health');
            $table->integer('initiative_roll')->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monster_instances');
    }
};
