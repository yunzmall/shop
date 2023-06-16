<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberFavoriteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_favorite')) {
            Schema::create('yz_member_favorite', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('member_id')->comment('会员ID');
                $table->integer('uniacid');
                $table->integer('goods_id')->comment('商品id');
                $table->integer('created_at');
                $table->boolean('deleted');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_favorite` comment '会员--会员喜欢的商品'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_favorite');
	}

}
