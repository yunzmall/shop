<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncomeIdIndexToYzWithdrawIncomeApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_withdraw_income_apply', function (Blueprint $table) {
			if (Schema::hasColumn('yz_withdraw_income_apply', 'income_id')) {
				$sm = Schema::getConnection()->getDoctrineSchemaManager();
				//获取该表的所有索引
				$all_index = $sm->listTableIndexes(app('db')->getTablePrefix().'yz_withdraw_income_apply');
				$flag = true;
				foreach ($all_index as $index) {
					if (in_array('income_id',$index->getColumns())) {
						$flag = false;
					}
				}
				//存在索引就不添加
				$flag && $table->index('income_id');
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
        Schema::table('yz_withdraw_income_apply', function (Blueprint $table) {
            //
        });
    }
}
