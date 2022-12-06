<?php

use App\Enums\UserRank;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonusSpToPackageConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_configs', function (Blueprint $table) {
            $table->integer('user_rank_id')->nullable()->default(UserRank::Regular);
            $table->double('bonus_sp')->nullable()->default(0);
            $table->double('bonus_rp')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_configs', function (Blueprint $table) {
            $table->dropColumn('user_rank_id');
            $table->dropColumn('bonus_sp');
            $table->dropColumn('bonus_rp');
        });
    }
}
