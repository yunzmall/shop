<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderMappingTable extends Migration {

	/**
	 * 在订单数据迁移时, 记录新旧order_id的对应关系
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_order_mapping')) {
            Schema::create('yz_order_mapping', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('old_order_id')->comment('旧订单ID');
                $table->integer('new_order_id')->comment('新订单ID');
                $table->char('old_openid', 50);
                $table->integer('new_member_id')->comment('新会员ID');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_order_mapping comment '会员--会员重构订单变化记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_order_mapping');
	}

}
