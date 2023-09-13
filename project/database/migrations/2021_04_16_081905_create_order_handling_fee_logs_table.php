<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHandlingFeeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_handling_fee_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('issuer_id');
            $table->integer('order_id');
            $table->integer('shop_id');
            $table->double('handling_fee_amount');
            $table->integer('handling_fee_rate');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_handling_fee_logs');
    }
}
