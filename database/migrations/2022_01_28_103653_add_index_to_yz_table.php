<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToYzTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_options', function (Blueprint $table) {
			if (Schema::hasColumn('yz_options', 'uniacid')) {
				$sm = Schema::getConnection()->getDoctrineSchemaManager();
				//获取该表的所有索引
				$all_index = $sm->listTableIndexes(app('db')->getTablePrefix().'yz_options');
				$flag = true;
				foreach ($all_index as $index) {
					if (in_array('uniacid',$index->getColumns())) {
						$flag = false;
					}
				}
				//存在索引就不添加
				$flag && $table->index('uniacid','uniacid_index');
			}
        });

		Schema::table('yz_member_cart', function (Blueprint $table) {
			if (Schema::hasColumn('yz_member_cart', 'uniacid') && Schema::hasColumn('yz_member_cart', 'goods_id')) {
				$sm = Schema::getConnection()->getDoctrineSchemaManager();
				//获取该表的所有索引
				$all_index = $sm->listTableIndexes(app('db')->getTablePrefix().'yz_member_cart');
				$uniacid = true;
				$goods_id = true;
				foreach ($all_index as $index) {
					if (in_array('uniacid',$index->getColumns())) {
						$uniacid = false;
					}
					if (in_array('goods_id',$index->getColumns())) {
						$goods_id = false;
					}
				}
				//存在索引就不添加
				$uniacid && $table->index('uniacid','uniacid_index');
				$goods_id && $table->index('goods_id','goods_id_index');
			}
		});

		Schema::table('yz_goods_coupon', function (Blueprint $table) {
			if (Schema::hasColumn('yz_goods_coupon', 'goods_id')) {
				$sm = Schema::getConnection()->getDoctrineSchemaManager();
				//获取该表的所有索引
				$all_index = $sm->listTableIndexes(app('db')->getTablePrefix().'yz_goods_coupon');
				$goods_id = true;
				foreach ($all_index as $index) {
					if (in_array('goods_id',$index->getColumns())) {
						$goods_id = false;
					}
				}
				//存在索引就不添加
				$goods_id && $table->index('goods_id','goods_id_index');
			}
		});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      	
    }
}
