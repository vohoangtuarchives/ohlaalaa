<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAdminTrackLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_admin_track_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('issuer_id');
            $table->integer('shop_id')->nullable();
            $table->integer('order_id');
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
        Schema::dropIfExists('order_admin_track_logs');
    }
}
