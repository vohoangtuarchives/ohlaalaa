<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopCouponCodeToVendorOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->string('shop_coupon_code')->nullable()->default(null);
            $table->double('shop_coupon_amount')->unsigned()->nullable()->default(null);
            $table->double('shop_coupon_value')->unsigned()->nullable()->default(null);
            $table->integer('shop_coupon_times')->unsigned()->nullable()->default(null);
            $table->integer('shop_coupon_used')->unsigned()->nullable()->default(null);
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
            $table->dropColumn('shop_coupon_code');
            $table->dropColumn('shop_coupon_amount');
            $table->dropColumn('shop_coupon_value');
            $table->dropColumn('shop_coupon_times');
            $table->dropColumn('shop_coupon_used');
        });
    }
}
