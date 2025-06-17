<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete existing characters (assumed to be old monsters) that have a null user_id
        DB::table('characters')->whereNull('user_id')->delete();

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->string('type')->default('player'); // Add back with a default
            $table->foreignId('user_id')->nullable()->change();
        });
    }
};
