<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKolConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kol_configs', function (Blueprint $table) {
            $table->id();
            $table->string('kol_date')->nullable();

            $table->double('con_bonus_l1')->nullable()->default(2.5);
            $table->integer('number_users_l1')->nullable()->default(70);
            $table->integer('number_orders_l1')->nullable()->default(70);
            $table->integer('number_shops_l1')->nullable()->default(3);
            $table->integer('number_affiliate_member_l1')->nullable()->default(3);
            $table->double('avg_amount_order_l1')->nullable()->default(0);

            $table->double('con_bonus_l2')->nullable()->default(1.5);
            $table->integer('number_users_l2')->nullable()->default(70);
            $table->integer('number_orders_l2')->nullable()->default(70);
            $table->integer('number_shops_l2')->nullable()->default(3);
            $table->integer('number_affiliate_member_l2')->nullable()->default(3);
            $table->double('avg_amount_order_l2')->nullable()->default(0);
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
        // Schema::table('kol_configs', function (Blueprint $table) {
        //     $table->dropColumn('kol_date');
        //     $table->dropColumn('con_bonus_l1');
        //     $table->dropColumn('con_bonus_l2');
        //     $table->dropColumn('number_users');
        //     $table->dropColumn('number_orders');
        //     $table->dropColumn('number_shops');
        //     $table->dropColumn('number_affiliate_member');
        //     $table->dropColumn('avg_amount_order');
        //     $table->dropColumn('total_amount');
            
        //     // $table->dropColumn('con_bonus_l2');
        //     // $table->dropColumn('number_user_l2');
        //     // $table->dropColumn('number_orders_l2');
        //     // $table->dropColumn('number_shops_l2');
        //     // $table->dropColumn('number_affiliate_member_l2');
        //     // $table->dropColumn('avg_amount_order_l2');
        // });
    }
}
