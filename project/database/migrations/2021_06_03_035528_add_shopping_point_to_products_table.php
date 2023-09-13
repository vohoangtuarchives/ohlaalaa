<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShoppingPointToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('price_shopping_point')->nullable()->default(0);
            $table->double('percent_price')->nullable()->default(0);
            $table->double('percent_shopping_point')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_shopping_point');
            $table->dropColumn('percent_price');
            $table->dropColumn('percent_shopping_point');
        });
    }
}
