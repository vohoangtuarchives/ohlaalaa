<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_url', 1000)->nullable();
            $table->double('price')->default(0);
            $table->string('remarks', 1000)->nullable();
            $table->text('content')->nullable();
            $table->integer('last_update_by');
            $table->integer('sort_index');
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
        Schema::dropIfExists('package_configs');
    }
}
