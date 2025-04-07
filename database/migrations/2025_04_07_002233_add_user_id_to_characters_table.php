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
		Schema::table('characters', function (Blueprint $table) {
			// Making it nullable initially to handle existing characters/monsters easier
			// Add constraint after the 'type' column if it exists, otherwise adjust
			$table->foreignId('user_id')->nullable()->after('type')->constrained('users')->nullOnDelete();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::table('characters', function (Blueprint $table) {
			// Drop foreign key constraint first before dropping column
			$table->dropForeign(['user_id']);
			$table->dropColumn('user_id');
		});
    }
};
