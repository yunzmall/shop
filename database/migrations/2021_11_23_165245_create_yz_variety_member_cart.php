<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzVarietyMemberCart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_variety_member_cart')) {
            Schema::create('yz_variety_member_cart', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('member_cart_id')->index()->comment('会员购物车id');
                $table->string('member_cart_type')->comment('会员购物车类型');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_variety_member_cart` comment '会员购物车关系表(多态)'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_variety_member_cart')) {
            Schema::dropIfExists('yz_variety_member_cart');
        }
    }
}
