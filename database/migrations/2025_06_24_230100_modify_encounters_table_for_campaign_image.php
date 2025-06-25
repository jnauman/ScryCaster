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
            // Remove the old current_image column if it exists
            // It's safer to check if the column exists before dropping
            if (Schema::hasColumn('encounters', 'current_image')) {
                $table->dropColumn('current_image');
            }

            // Add the new foreign key for selected campaign image
            // Make it nullable and set null on delete of the campaign image
            $table->foreignId('selected_campaign_image_id')
                  ->nullable()
                  ->after('campaign_id') // Or choose another position
                  ->constrained('campaign_images')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            // Drop the foreign key and the column
            $table->dropForeign(['selected_campaign_image_id']);
            $table->dropColumn('selected_campaign_image_id');

            // Add back the old current_image column if needed for rollback
            // You might want to make this nullable or define a default
            if (!Schema::hasColumn('encounters', 'current_image')) {
                $table->string('current_image')->nullable()->after('campaign_id');
            }
        });
    }
};
