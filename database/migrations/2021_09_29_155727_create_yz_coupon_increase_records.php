<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzCouponIncreaseRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_coupon_increase_records')) {
            Schema::create('yz_coupon_increase_records', function (Blueprint $table) {
                $table->increments('id')->unique();
                $table->integer('uniacid');
                $table->integer('count')->comment('每次新增的数量');
                $table->integer('coupon_id')->index()->comment('优惠券id');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
        }
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_coupon_increase_records comment '优惠券新增记录表'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
