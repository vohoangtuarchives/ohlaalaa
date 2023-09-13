<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKolAffiliateCalculatedToMemberPackageRegisters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_package_registers', function (Blueprint $table) {
            $table->boolean('kolaff_calculated')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_package_registers', function (Blueprint $table) {
            $table->dropColumn('kolaff_calculated');
        });
    }
}
