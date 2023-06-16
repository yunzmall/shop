<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzMinAppPayManageOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_min_app_pay_manage_order')) {
            Schema::create('yz_min_app_pay_manage_order',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('uniacid')->comment('公众号')->index('uniacid_idx');
                    $table->string('trade_no', 100)->comment('商家交易单号')->index('trade_nox');
                    $table->string('openid')->nullable()->comment('该交易用户的openid');
                    $table->string('transaction_id')->nullable()->comment('支付单号');
                    $table->string('divide_no')->nullable()->comment('商家分账单号(唯一)');
                    $table->string('error_msg')->nullable()->comment('接口返回错误信息');
                    $table->text('notice_params')->nullable()->comment('异步回调通知参数');
                    $table->text('mark')->nullable()->comment('请求接口备注');
                    $table->tinyInteger('status')->default(0)->comment('状态：0未支付1已支付2已分账');
                    $table->integer('pay_time')->nullable();
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_min_app_pay_manage_order` comment'系统--小程序支付管理订单'");//表注释

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_min_app_pay_manage_order');
    }
}
