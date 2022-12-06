<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsumerMoneyCollectionDateToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('consumer_money_collection_date')->nullable()->default(null);
            $table->string('consumer_money_collection_bank')->nullable()->default(null);
            $table->date('payment_to_company_date')->nullable()->default(null);
            $table->string('payment_to_company_partner')->nullable()->default(null);
            $table->double('payment_to_company_amount')->nullable()->default(null);
            $table->date('payment_to_merchant_date')->nullable()->default(null);
            $table->double('payment_to_merchant_amount')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('consumer_money_collection_date');
            $table->dropColumn('consumer_money_collection_bank');
            $table->dropColumn('payment_to_company_date');
            $table->dropColumn('payment_to_company_partner');
            $table->dropColumn('payment_to_company_amount');
            $table->dropColumn('payment_to_merchant_date');
            $table->dropColumn('payment_to_merchant_amount');
        });
    }
}
