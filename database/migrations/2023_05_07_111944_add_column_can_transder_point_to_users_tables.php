<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCanTransderPointToUsersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean("can_transfer_point")->default(false);
            $table->double("max_transfer_point")->default(0);
            $table->double("transfered_shopping_point")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("can_transfer_point");
            $table->dropColumn("max_transfer_point");
            $table->dropColumn("transfered_shopping_point");
        });
    }
}
