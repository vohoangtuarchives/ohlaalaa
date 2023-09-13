<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToDevelopmentNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('development_notes', function (Blueprint $table) {
            $table->string('title', 50)->nullable()->default(null);
            $table->string('code', 50)->nullable()->default(null);
            $table->text('content')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('development_notes', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('code');
            $table->dropColumn('content');
        });
    }
}
