<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerCityIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('customer_province_id')->nullable();
            $table->integer('customer_district_id')->nullable();
            $table->integer('customer_ward_id')->nullable();
            $table->boolean('is_send_fee')->nullable();
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
            $table->dropColumn('customer_province_id');
            $table->dropColumn('customer_district_id');
            $table->dropColumn('customer_ward_id');
            $table->dropColumn('is_send_fee');
        });
    }
}
