<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentNumberToMemberPackageRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_package_registers', function (Blueprint $table) {
            $table->string('payment_number', 50)->nullable()->default(null);
            $table->string('payment_status', 50)->nullable()->default(null);
            $table->string('payment_bank', 50)->nullable()->default(null);
            $table->dateTime('payment_complete_at')->nullable()->default(null);
            $table->string('payment_note', 500)->nullable()->default(null);
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
            $table->dropColumn('payment_number');
            $table->dropColumn('payment_status');
            $table->dropColumn('payment_bank');
            $table->dropColumn('payment_complete_at');
            $table->dropColumn('payment_note');
        });
    }
}
