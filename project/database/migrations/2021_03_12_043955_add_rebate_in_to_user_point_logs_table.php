<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRebateInToUserPointLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_point_logs', function (Blueprint $table) {
            $table->double('rebate_bonus')->nullable();
            $table->integer('rebate_in')->nullable();
            $table->integer('rebate_payment_in')->nullable();
            $table->double('daily_sp_exchange_rate')->nullable();
            $table->integer('affiliate_exchange_in')->nullable();
            $table->double('sp_vnd_exchange_rate')->nullable();
            $table->double('handling_fee_rate')->nullable();
            $table->double('merchant_sale_bonus')->nullable();
            $table->integer('merchant_sale_bonus_in')->nullable();
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
            $table->dropColumn('rebate_bonus');
            $table->dropColumn('rebate_in');
            $table->dropColumn('rebate_payment_in');
            $table->dropColumn('daily_sp_exchange_rate');
            $table->dropColumn('affiliate_exchange_in');
            $table->dropColumn('sp_vnd_exchange_rate');
            $table->dropColumn('handling_fee_rate');
            $table->dropColumn('merchant_sale_bonus');
            $table->dropColumn('merchant_sale_bonus_in');
        });
    }
}
