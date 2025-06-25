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
            $table->string('class')->nullable();
            $table->string('ancestry')->nullable();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->dropColumn('current_health');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['class', 'ancestry', 'title', 'image']);
            $table->integer('current_health')->nullable();
        });
    }
};
