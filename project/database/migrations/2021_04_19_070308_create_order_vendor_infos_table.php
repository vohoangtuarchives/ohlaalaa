<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderVendorInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_vendor_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('shop_id');
            $table->date('payment_to_merchant_date')->nullable()->default(null);
            $table->double('payment_to_merchant_amount')->nullable()->default(null);
            $table->string('remarks', 1000)->nullable();
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
        Schema::dropIfExists('order_vendor_infos');
    }
}
