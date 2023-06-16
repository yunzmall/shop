<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzWechatWithdrawLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_wechat_withdraw_log')) {
            Schema::create('yz_wechat_withdraw_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid')->comment('平台ID')->index('idx_uniacid');
                $table->string('withdraw_sn')->comment('提现单号');
                $table->string('out_batch_no')->comment('商家批次单号');
                $table->string('out_detail_no')->comment('商家明细单号|提现订单号');
                $table->string('batch_id')->nullable()->comment('微信批次单号');
                $table->string('batch_status')->nullable()->comment('微信批次状态:状态值看微信文档');
                $table->string('detail_id')->nullable()->comment('微信明细单号');
                $table->string('detail_status')->nullable()->comment('明细状态:状态值看微信文档');
                $table->string('http_code')->nullable()->comment('请求状态码');
                $table->string('fail_code')->nullable()->comment('错误码,需要用于判断是否用原先的单号重试');
                $table->string('fail_msg')->nullable()->comment('错误信息');
                $table->tinyInteger('status')->default(0)->comment('状态：-2失败关闭不再操作，0未发送提现，1提现处理中，2提现完成不再操作');
                $table->tinyInteger('type')->default(0)->comment('类型：0商城提现，1供应商提现');
                $table->tinyInteger('pay_type')->comment('配置类型：1公众号，2小程序，3app');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();

            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_wechat_withdraw_log` comment '系统--微信提现记录表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_wechat_withdraw_log');
    }
}
