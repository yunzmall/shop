<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberCartTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_cart')) {
            Schema::create('yz_member_cart', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('member_id')->comment('会员id');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('goods_id')->comment('商品id');
                $table->integer('total')->comment('数量');
                $table->integer('price')->comment('价格');
                $table->integer('option_id')->comment('规格id');
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_cart` comment '会员--购物车'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_cart');
	}

}
