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
            $table->integer('torch_timer_duration')->nullable()->comment('Total duration of the torch in minutes');
            $table->integer('torch_timer_remaining')->nullable()->comment('Remaining time for the torch in minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            $table->dropColumn('torch_timer_duration');
            $table->dropColumn('torch_timer_remaining');
        });
    }
};
