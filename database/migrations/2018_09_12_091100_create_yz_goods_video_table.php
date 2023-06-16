<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzGoodsVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_video')) {
        	Schema::create('yz_goods_video', function (Blueprint $table) {
        		$table->integer('id', true);
        		$table->integer('goods_id')->nullable()->default(0)->index('idx_goods');
        		$table->string('goods_video', 255)->nullable()->default('')->comment('视频地址');
        		$table->string('video_image', 200)->nullable()->default('')->comment('视频封面地址');
        		$table->tinyInteger('status')->default(0)->comment('该字段已废弃');
        		$table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
        	});
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_goods_video comment '商品视频设置记录表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_goods_video');
    }
}
