<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOrderGoodsTaxFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_goods_tax_fee')) {
            Schema::create('yz_order_goods_tax_fee', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid');
                $table->integer('order_goods_id')->comment('订单商品id');
                $table->integer('uid');
                $table->decimal('amount',11,2)->comment('税费金额，负数优惠，正数加钱');
                $table->string('fee_code')->comment('税费码');
                $table->string('name')->comment('税费名');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_order_goods_tax_fee` comment '订单商品税费记录表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_goods_tax_fee');
    }
}
