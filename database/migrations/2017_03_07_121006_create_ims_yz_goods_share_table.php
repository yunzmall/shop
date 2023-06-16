<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzGoodsShareTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_goods_share')) {
            Schema::create('yz_goods_share', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('goods_id')->index('idx_goodid');
                $table->boolean('need_follow')->nullable()->comment('强制关注,1为开启，0为关闭');
                $table->string('no_follow_message')->nullable()->default('')->comment('未关注提示信息');
                $table->string('follow_message')->nullable()->default('')->comment('关注引导信息');
                $table->string('share_title', 50)->nullable()->default('')->comment('分享标题');
                $table->string('share_thumb')->nullable()->default('')->comment('分享图片');
                $table->string('share_desc')->nullable()->default('')->comment('分享描述');
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_goods_share comment '商品分享关注设置表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_goods_share');
	}

}
