<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPaidRebateToVendorOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->boolean('is_rebate_paid')->nullable();
            $table->double('rebate_bonus')->nullable();
            $table->double('rebate_amount')->nullable();
            $table->double('rebate_in')->nullable();
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
            $table->dropColumn('is_rebate_paid');
            $table->dropColumn('rebate_bonus');
            $table->dropColumn('rebate_amount');
            $table->dropColumn('rebate_in');
        });
    }
}
