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
		Schema::table('encounters', function (Blueprint $table) {
			// Nullable allows encounters outside campaigns if needed
			// Add after 'name' column or adjust as needed
			$table->foreignId('campaign_id')->nullable()->after('name')->constrained('campaigns')->nullOnDelete();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::table('encounters', function (Blueprint $table) {
			$table->dropForeign(['campaign_id']);
			$table->dropColumn('campaign_id');
		});
    }
};
