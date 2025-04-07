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
		Schema::create('campaign_character', function (Blueprint $table) {
			$table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
			$table->foreignId('character_id')->constrained()->cascadeOnDelete();
			$table->primary(['campaign_id', 'character_id']); // Composite primary key
			// No timestamps needed typically for a simple pivot
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_character');
    }
};
