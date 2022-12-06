<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMerchantSaleBonusNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_merchant_sale_bonus_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('issuer_id');
            $table->double('merchant_sale_bonus');
            $table->integer('merchant_sale_bonus_in');
            $table->double('sp_vnd_exchange_rate');
            $table->string('note');
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
        Schema::dropIfExists('user_merchant_sale_bonus_notes');
    }
}
