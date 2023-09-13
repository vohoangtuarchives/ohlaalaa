<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectByToMemberPackageRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_package_registers', function (Blueprint $table) {
            $table->dateTime('rejected_at')->nullable()->default(null);
            $table->integer('rejected_by')->unsigned()->nullable()->default(null);
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
            $table->dropColumn('rejected_at');
            $table->dropColumn('rejected_by');
        });
    }
}
