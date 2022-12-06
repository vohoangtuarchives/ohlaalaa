<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPackageAlepayTrackLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_package_alepay_track_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('mpr_id');
            $table->string('title')->nullable()->default(null);
            $table->text('content')->nullable()->default(null);
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
        Schema::dropIfExists('member_package_alepay_track_logs');
    }
}
