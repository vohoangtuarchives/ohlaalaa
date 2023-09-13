<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_vendors', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('code');
            $table->integer('type')->default(0);
            $table->double('price')->default(0);
            $table->integer('times')->unsigned()->nullable()->default(null);
            $table->integer('used')->unsigned()->default(0);
            $table->integer('status')->unsigned()->default(0);
            $table->string('remarks', 500)->nullable()->default(null);
            $table->date('start_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
            $table->integer('create_by')->nullable();
            $table->integer('admin_issuer_id')->nullable();
            $table->dateTime('admin_updated_date')->nullable()->default(null);
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
        Schema::dropIfExists('coupon_vendors');
    }
}
