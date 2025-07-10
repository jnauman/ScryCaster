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
        Schema::table('monster_instances', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('monster_id');
            $table->string('initiative_group')->nullable()->after('initiative_roll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monster_instances', function (Blueprint $table) {
            $table->dropColumn('display_name');
            $table->dropColumn('initiative_group');
        });
    }
};
