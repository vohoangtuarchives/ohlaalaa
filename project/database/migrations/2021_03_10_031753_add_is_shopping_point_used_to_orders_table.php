<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsShoppingPointUsedToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_shopping_point_used')->nullable();
            $table->double('shopping_point_payment_remain')->nullable();
            $table->double('shopping_point_used')->nullable();
            $table->double('shopping_point_amount')->nullable();
            $table->double('shopping_point_exchange_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_shopping_point_used');
            $table->dropColumn('shopping_point_payment_remain');
            $table->dropColumn('shopping_point_used');
            $table->dropColumn('shopping_point_amount');
            $table->dropColumn('shopping_point_exchange_rate');
        });
    }
}
