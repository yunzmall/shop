<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzGoodsSpecInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_spec_info')) {
            Schema::create('yz_goods_spec_info', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid');
                $table->integer('goods_id')->comment('商品ID');
                $table->string('info_img')->comment('图片');
                $table->text('content')->comment('内容');
                $table->integer('goods_option_id')->nullable()->comment('规格ID');
                $table->integer('sort')->default(0)->comment('排序');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_goods_spec_info` comment '商品--商品规格信息表'");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_goods_spec_info')) {
            Schema::dropIfExists('yz_goods_spec_info');
        }
    }
}
