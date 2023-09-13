<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevenueToKolConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kol_configs', function (Blueprint $table) {
            $table->double('revenue_l1')->nullable()->default(0);
            $table->double('revenue_l2')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kol_configs', function (Blueprint $table) {
            $table->dropColumn('revenue_l1');
            $table->dropColumn('revenue_l2');
        });
    }
}
