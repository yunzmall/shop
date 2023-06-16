<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderGoodsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_goods')) {
            Schema::create('yz_order_goods', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->integer('order_id')->default(0)->comment('订单ID');
                $table->integer('goods_id')->default(0)->comment('商品id');
                $table->integer('total')->default(1)->comment('购买数量');
                $table->integer('create_at')->default(0);
                $table->integer('price')->default(0)->comment('订单金额计算基数');
                $table->string('goods_sn', 50)->default('')->comment('商品码');
                $table->string('thumb', 50)->comment('商品图片');
                $table->string('title', 50)->comment('商品名称');
                $table->integer('goods_price')->default(0)->comment('商品现价');
                $table->integer('goods_option_id')->comment('规格ID');
                $table->string('goods_option_title',255)->default('')->comment('规格名称');
                $table->integer('product_sn')->comment('商品条码');
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_goods` comment'订单--商品记录'");//表注释

        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_goods');
    }

}
