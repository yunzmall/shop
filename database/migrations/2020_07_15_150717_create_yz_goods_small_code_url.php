<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzGoodsSmallCodeUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_small_code_url')) {
            Schema::create('yz_goods_small_code_url', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号');
                $table->integer('goods_id')->comment('商品ID');
                $table->string('collect_small_url')->comment('小程序二维码');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_goods_small_code_url comment '商品小程序二维码记录表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_goods_small_code_url');
    }
}
