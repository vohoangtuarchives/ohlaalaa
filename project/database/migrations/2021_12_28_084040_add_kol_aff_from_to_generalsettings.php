<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKolAffFromToGeneralsettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->date('kol_aff_from')->nullable()->default('2021-12-01');
            $table->double('kol_aff_bonus')->nullable()->default(10);
            $table->date('kol_con_from')->nullable()->default('2021-12-01');
            $table->double('kol_con_bonus')->nullable()->default(2.5);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->dropColumn('kol_aff_from');
            $table->dropColumn('kol_aff_bonus');
            $table->dropColumn('kol_con_from');
            $table->dropColumn('kol_con_bonus');
        });
    }
}
