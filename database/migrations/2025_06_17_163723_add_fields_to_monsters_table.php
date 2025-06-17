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
        Schema::table('monsters', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->string('armor_type')->nullable()->after('ac'); // ac already exists
            // max_health already exists
            $table->string('attacks')->nullable()->after('max_health');
            $table->string('movement')->nullable()->after('attacks');
            // strength, dexterity, constitution, intelligence, wisdom, charisma already exist
            $table->string('alignment', 50)->nullable()->after('charisma');
            $table->unsignedInteger('level')->nullable()->after('alignment');
            $table->json('traits')->nullable()->after('level');
            // 'data' field is assumed to exist for additional non-structured data
            // 'user_id' is assumed to exist

            // Adjust existing columns if necessary, e.g., making them nullable
            $table->integer('ac')->nullable()->change();
            $table->integer('max_health')->nullable()->change();
            $table->integer('strength')->nullable()->change();
            $table->integer('dexterity')->nullable()->change();
            $table->integer('constitution')->nullable()->change();
            $table->integer('intelligence')->nullable()->change();
            $table->integer('wisdom')->nullable()->change();
            $table->integer('charisma')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'description',
                'armor_type',
                'attacks',
                'movement',
                'alignment',
                'level',
                'traits',
            ]);

            // Revert changes to existing columns if necessary
            // This depends on their original definition
            // For example, if they were not nullable before:
            // $table->integer('ac')->nullable(false)->change();
            // ... and so on for other columns.
            // For simplicity, this example assumes they can remain nullable on rollback.
        });
    }
};
