<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateImsYzPointRechargeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_point_recharge')) {
            Schema::create('yz_point_recharge', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable();
                $table->integer('member_id')->nullable()->comment('会员id');
                $table->decimal('money', 14)->nullable()->comment('充值数量');
                $table->integer('type')->nullable()->comment('充值方式');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->string('order_sn', 50)->nullable()->comment('充值单号');
                $table->boolean('status')->nullable()->default(0)->comment('充值状态');
                $table->string('remark', 50)->comment('备注');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_point_recharge` comment '积分--积分充值明细表'");
            $records = \app\common\models\finance\PointLog::where('point_mode', 5)->get();
            foreach ($records as $key => $record) {
                \Illuminate\Support\Facades\DB::table('yz_point_recharge')->insert(
                    [
                        'uniacid' => $record->uniacid,
                        'member_id' => $record->member_id,
                        'money' => $record->point,
                        'type' => 0,
                        'created_at' => time(),
                        'updated_at' => time(),
                        'order_sn' => 'RP20181022000000000000',
                        'status' => 1,
                        'remark' => '明细记录迁移',
                    ]
                );
            }
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ims_yz_point_recharge');
	}

}
