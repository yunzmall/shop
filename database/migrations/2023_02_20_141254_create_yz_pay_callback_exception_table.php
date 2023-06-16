<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzPayCallbackExceptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_pay_callback_exception')) {
            Schema::create('yz_pay_callback_exception', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->string('pay_sn',100)->comment('支付商户号');
                $table->integer('pay_type_id')->comment('支付类型ID');
                $table->integer('error_code')->default(0)->comment('错误类型');
                $table->string('error_msg')->default('')->comment('错误提示');
                $table->string('handle_msg')->default('')->comment('手动处理失败提示');
                $table->text('result')->nullable()->comment('支付回调处理参数');
                $table->text('response')->nullable()->comment('异步响应数据');
                $table->tinyInteger('status')->default(0)->comment('处理状态：0未处理');
                $table->integer('frequency')->default(0)->comment('异常频率');
                $table->integer('record_at')->nullable()->comment('记录时间');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_pay_callback_exception` comment'订单--支付异步订单报错'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_pay_callback_exception');
    }
}
