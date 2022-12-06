<?php

use App\Enums\RankingLogType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRankingLogTypeInUserRankingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_ranking_logs', function (Blueprint $table) {
            $table->integer('ranking_log_type')->unsigned()->nullable()->default(RankingLogType::Other)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_ranking_logs', function (Blueprint $table) {
            //
        });
    }
}
