<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsAdvertising extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_advertising')) {
            Schema::create('yz_goods_advertising', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid');
                $table->integer('goods_id')->index();
                $table->integer('is_open')->default("0")->comment('广告宣传语开关');
                $table->string('copywriting')->default("")->comment('文案');
                $table->integer('created_at')->nullable()->comment('创建时间');
                $table->integer('updated_at')->nullable()->comment('修改时间');
            });
        }
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_goods_advertising` comment'商品--广告宣传语记录表'");//表注释
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
