<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzPostageIncludedCategoryGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_postage_included_category_goods')) {
            Schema::create('yz_postage_included_category_goods', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('postage_included_category_id')->index('postage_included_category_id')->comment('包邮分类ID');
                $table->integer('goods_id')->index('goods_id')->comment('商品ID');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_postage_included_category_goods` comment '包邮分类商品关系表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_postage_included_category_goods')) {
            Schema::dropIfExists('yz_postage_included_category_goods');
        }
    }
}
