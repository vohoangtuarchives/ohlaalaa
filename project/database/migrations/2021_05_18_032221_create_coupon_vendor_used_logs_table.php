<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponVendorUsedLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_vendor_used_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('coupon_id');
            $table->integer('user_id');
            $table->string('code');
            $table->integer('type')->default(0);
            $table->double('price')->default(0);
            $table->integer('times')->unsigned()->nullable()->default(null);
            $table->integer('used')->unsigned()->default(0);
            $table->string('remarks', 500)->nullable()->default(null);
            $table->string('descriptions', 500)->nullable()->default(null);
            $table->date('start_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
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
        Schema::dropIfExists('coupon_vendor_used_logs');
    }
}
