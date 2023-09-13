<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDebtAmountToOrderVendorInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendor_infos', function (Blueprint $table) {
            $table->double('debt_amount')->nullable();
            $table->boolean('is_debt')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendor_infos', function (Blueprint $table) {
            $table->dropColumn('debt_amount');
            $table->dropColumn('is_debt');
        });
    }
}
