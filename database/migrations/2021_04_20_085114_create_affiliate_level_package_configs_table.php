<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateLevelPackageConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_level_package_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('affiliate_level_id');
            $table->integer('package_config_id');
            $table->double('affiliate_bonus')->default(0);
            $table->integer('last_update_by');
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
        Schema::dropIfExists('affiliate_level_package_configs');
    }
}
