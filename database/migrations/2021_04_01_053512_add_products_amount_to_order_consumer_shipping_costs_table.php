<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductsAmountToOrderConsumerShippingCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_consumer_shipping_costs', function (Blueprint $table) {
            $table->double('products_amount')->nullable()->default(0);
            $table->double('shopping_point_amount')->nullable()->default(0);
            $table->double('tax_amount')->nullable()->default(0);
            $table->double('discount_amount')->nullable()->default(0);
            $table->integer('total_qty')->nullable()->default(0);
            $table->integer('total_item')->nullable()->default(0);
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
            $table->dropColumn('products_amount');
            $table->dropColumn('shopping_point_amount');
            $table->dropColumn('tax_amount');
            $table->dropColumn('discount_amount');
            $table->dropColumn('total_qty');
            $table->dropColumn('total_item');
        });
    }
}
