<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderConsumerShippingCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_consumer_shipping_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained();
            $table->foreignId('from_city_id')->constrained();
            $table->foreignId('from_district_id')->constrained();
            $table->foreignId('consumer_id')->constrained();
            $table->foreignId('to_city_id')->constrained();
            $table->foreignId('to_district_id')->constrained();
            $table->foreignId('to_ward_id')->constrained();
            $table->string('shipping_partner');
            $table->string('shipping_partner_code')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('MONEY_COLLECTION')->nullable();
            $table->double('EXCHANGE_WEIGHT')->nullable();
            $table->double('MONEY_TOTAL_FEE')->nullable();
            $table->double('MONEY_FEE')->nullable();
            $table->double('MONEY_COLLECTION_FEE')->nullable();
            $table->double('MONEY_OTHER_FEE')->nullable();
            $table->double('MONEY_VAS')->nullable();
            $table->double('MONEY_VAT')->nullable();
            $table->double('KPI_HT')->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('order_consumer_shipping_costs');
    }
}
