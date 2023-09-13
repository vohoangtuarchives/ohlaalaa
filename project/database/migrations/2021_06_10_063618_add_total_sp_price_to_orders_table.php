<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalSpPriceToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public $totalSPPriceRemainAmount = 0;
    public $totalSPUsed = 0;
    public $totalSPAmount = 0;
    public $totalShopCouponAmount = 0;
    public $totalProductSubAmount = 0;
    public $totalProductFinalAmount = 0;
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('total_sp_price')->nullable()->default(0);
            $table->double('total_sp_price_amount')->nullable()->default(0);
            $table->double('total_sp_price_remain_amount')->nullable()->default(0);
            $table->double('total_product_sub_amount')->nullable()->default(0);
            $table->double('total_product_final_amount')->nullable()->default(0);
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
            $table->dropColumn('total_sp_price');
            $table->dropColumn('total_sp_price_amount');
            $table->dropColumn('total_sp_price_remain_amount');
            $table->dropColumn('total_product_sub_amount');
            $table->dropColumn('total_product_final_amount');
        });
    }
}
