<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPackageRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_package_registers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('package_config_id');
            $table->double('package_price')->default(0);
            $table->integer('approval_status')->default(1);
            $table->dateTime('approval_at')->nullable()->default(null);
            $table->integer('approved_by')->nullable()->default(null);
            $table->dateTime('package_old_end_at')->nullable()->default(null);
            $table->dateTime('package_new_end_at')->nullable()->default(null);
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
        Schema::dropIfExists('member_package_registers');
    }
}
