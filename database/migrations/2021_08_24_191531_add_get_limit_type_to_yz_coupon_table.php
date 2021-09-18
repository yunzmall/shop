<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGetLimitTypeToYzCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('yz_coupon')){
            Schema::table('yz_coupon', function (Blueprint $table) {
                if(!Schema::hasColumn('yz_coupon','get_limit_type')){
                    $table->tinyInteger('get_limit_type')->after('get_max')->default(0)->comment('每人每日限领状态');
                }
                if(!Schema::hasColumn('yz_coupon','get_limit_max')){
                    $table->integer('get_limit_max')->after('get_max')->default(0)->comment('每人每日限领数量');
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
        Schema::table('yz_coupon', function (Blueprint $table) {
            //
        });
    }
}
