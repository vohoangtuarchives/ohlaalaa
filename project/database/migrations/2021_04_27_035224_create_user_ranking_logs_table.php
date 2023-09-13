<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRankingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ranking_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ranking_log_type')->nullable()->default(null);
            $table->string('from_rank')->nullable()->default(null);
            $table->string('to_rank')->nullable()->default(null);
            $table->string('remarks', 500)->nullable()->default(null);
            $table->string('descriptions', 1000)->nullable()->default(null);
            $table->integer('admin_issuer')->nullable()->default(null);
            $table->integer('user_issuer')->nullable()->default(null);
            $table->integer('member_package_register_id')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_ranking_logs');
    }
}
