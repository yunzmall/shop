<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsYzWechatProfitSharingLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_wechat_profit_sharing_log')) {
            Schema::create('yz_wechat_profit_sharing_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable();
                $table->integer('order_id')->nullable()->comment('订单ID');
                $table->string('mch_id')->nullable()->comment('商户号');
                $table->string('sub_mch_id')->nullable()->comment('子商户号');
                $table->string('appid')->nullable()->comment('商户appid');
                $table->string('sub_appid')->nullable()->comment('子商户appid');
                $table->integer('type')->nullable()->comment('分账类型');
                $table->integer('account')->nullable()->comment('分账接收方');
                $table->string('transaction_id')->nullable()->comment('微信支付单号');
                $table->string('out_order_no')->nullable()->comment('分账编号');
                $table->string('description')->nullable()->comment('分账描述');
                $table->integer('amount')->nullable()->comment('分账金额，单位分');
                $table->integer('status')->nullable()->comment('分账状态');
                $table->string('message')->nullable()->comment('回调信息');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_wechat_profit_sharing_log comment '微信服务商分账记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ims_yz_excel_recharge_detail');
	}

}
