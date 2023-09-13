<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpVndExchangeRateToUserConvertPointNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_convert_point_notes', function (Blueprint $table) {
            $table->double('sp_vnd_exchange_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_convert_point_notes', function (Blueprint $table) {
            $table->dropColumn('sp_vnd_exchange_rate');
        });
    }
}
