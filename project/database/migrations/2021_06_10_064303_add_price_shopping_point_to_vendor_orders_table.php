<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceShoppingPointToVendorOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->double('price_shopping_point')->nullable()->default(0);
            $table->double('price_shopping_point_amount')->nullable()->default(0);
            $table->double('percent_shopping_point')->nullable()->default(0);
            $table->double('item_price_shopping_point')->nullable()->default(0);
            $table->double('product_sub_amount')->nullable()->default(0);
            $table->double('product_final_amount')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->dropColumn('price_shopping_point');
            $table->dropColumn('price_shopping_point_amount');
            $table->dropColumn('percent_shopping_point');
            $table->dropColumn('item_price_shopping_point');
            $table->dropColumn('product_sub_amount');
            $table->dropColumn('product_final_amount');
        });
    }
}
