<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzGoodsContactTel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_contact_tel')) {
            Schema::create('yz_goods_contact_tel', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->integer('goods_id')->comment('商品id');
                $table->string('contact_tel')->nullable()->comment('联系电话');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_goods_contact_tel` comment'订单--支付异步订单报错'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_goods_contact_tel');
    }
}
