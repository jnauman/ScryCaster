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
		Schema::create('encounter_character', function (Blueprint $table) {
			$table->id();
			$table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
			$table->foreignId('character_id')->constrained()->cascadeOnDelete();
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
		Schema::dropIfExists('encounter_character');
	}
};