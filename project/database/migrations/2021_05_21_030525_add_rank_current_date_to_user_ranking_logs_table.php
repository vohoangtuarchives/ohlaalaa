<?php

use App\Enums\UserRank;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRankCurrentDateToUserRankingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_ranking_logs', function (Blueprint $table) {
            $table->dateTime('rank_current_date')->nullable()->default(null);
            $table->dateTime('rank_end_date')->nullable()->default(null);
            $table->integer('rank_ad_applied')->nullable()->default(null);
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
            $table->dropColumn('rank_current_date');
            $table->dropColumn('rank_end_date');
            $table->dropColumn('rank_ad_applied');
        });
    }
}
