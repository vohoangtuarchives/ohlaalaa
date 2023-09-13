<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayAmount3ToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('pay_amount3')->nullable()->default(0);
            $table->double('pay_amount4')->nullable()->default(0);
            $table->double('pay_cost')->nullable()->default(0);
            $table->double('pay_discount')->nullable()->default(0);
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
            $table->dropColumn('pay_amount3');
            $table->dropColumn('pay_amount4');
            $table->dropColumn('pay_cost');
            $table->dropColumn('pay_discount');
        });
    }
}
