<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopIdToUserPointLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_point_logs', function (Blueprint $table) {
            $table->integer('shop_id')->nullable();
            $table->double('amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_point_logs', function (Blueprint $table) {
            $table->dropColumn('shop_id');
            $table->dropColumn('amount');
        });
    }
}
