<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovedByToUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dateTime('approved_at')->nullable()->default(null);
            $table->dateTime('rejected_at')->nullable()->default(null);
            $table->string('approved_by', 100)->nullable()->default(null);
            $table->string('rejected_by', 100)->nullable()->default(null);
            $table->date('old_end_at')->nullable()->default(null);
            $table->date('new_end_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->dropColumn('rejected_at');
            $table->dropColumn('approved_by');
            $table->dropColumn('rejected_by');
            $table->dropColumn('old_end_at');
            $table->dropColumn('new_end_at');
        });
    }
}
