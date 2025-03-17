<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
	public function up()
	{
		Schema::table('characters', function (Blueprint $table) {
			$table->integer('strength')->nullable();
			$table->integer('dexterity')->nullable();
			$table->integer('constitution')->nullable();
			$table->integer('intelligence')->nullable();
			$table->integer('wisdom')->nullable();
			$table->integer('charisma')->nullable();
		});
	}

	public function down()
	{
		Schema::table('characters', function (Blueprint $table) {
			$table->dropColumn(['strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma']);
		});
	}
};
