<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryServiceToOrderConsumerShippingCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_consumer_shipping_costs', function (Blueprint $table) {
            $table->string('delivery_service')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_consumer_shipping_costs', function (Blueprint $table) {
            $table->dropColumn('delivery_service');
        });
    }
}
