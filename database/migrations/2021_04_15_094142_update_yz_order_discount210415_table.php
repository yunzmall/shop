<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateYzOrderDiscount210415Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_discount')) {
            Schema::table('yz_order_discount', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_discount', 'no_show')) {
                    $table->tinyInteger('no_show')->default(0)->comment('该优惠项目不在预下单显示，0否1是');
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_order_discount', function (Blueprint $table) {
            $table->dropColumn('no_show');
        });
    }
}
