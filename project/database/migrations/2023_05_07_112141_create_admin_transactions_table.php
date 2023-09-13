<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("admin_id");
            $table->foreign("admin_id")->references("id")->on("admins");
            $table->string("admin_name");
            $table->string("name");
            $table->text("content");
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
        Schema::dropIfExists('admin_transactions');
    }
}
