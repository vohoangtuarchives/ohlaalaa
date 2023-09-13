<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPharse2FieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->double('shopping_point')->nullable();
            $table->double('reward_point')->nullable();
            $table->string('referral_code')->nullable();
            $table->string('referral_user_id')->nullable();
            $table->string('referral_user_ids', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('shopping_point');
            $table->dropColumn('reward_point');
            $table->dropColumn('referral_code');
            $table->dropColumn('referral_user_id');
            $table->dropColumn('referral_user_ids');
        });
    }
}
