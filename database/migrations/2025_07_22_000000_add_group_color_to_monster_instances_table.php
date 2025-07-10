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
            $table->string('group_color')->nullable()->after('initiative_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monster_instances', function (Blueprint $table) {
            $table->dropColumn('group_color');
        });
    }
};
