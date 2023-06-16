<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzBusinessMessageNoticeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('yz_business_message_notice')) {
            Schema::create('yz_business_message_notice', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->nullable();
                $table->integer('business_id')->nullable()->comment('企业ID')->index('business_idx');
                $table->integer('recipient_id')->comment('消息接收人员工ID')->index('recipient_idx');
                $table->integer('operate_id')->default(0)->comment('发起消息员工ID');
                $table->string('plugin',150)->default('')->comment('唯一服务容器标识，消息所属应用');
                $table->string('notice_type',150)->default('')->comment('消息通知类型');
                $table->longText('param')->nullable()->comment('消息参数保存，json格式保存');
                $table->text('html')->nullable()->comment('给需要保存大数据的消息');
                $table->tinyInteger('handle_status')->default(0)->comment('状态：0已处理1待处理');
                $table->tinyInteger('status')->default(0)->comment('状态：0未读1已读');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_business_message_notice` comment '企业PC端--企业消息通知'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_business_message_notice');
    }
}
