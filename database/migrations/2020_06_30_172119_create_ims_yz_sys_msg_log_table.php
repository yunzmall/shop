<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzSysMsgLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_sys_msg_log')) {
            Schema::create('yz_sys_msg_log', function (Blueprint $table) {
                //系统消息列表
                $table->increments('id');
                $table->integer('uniacid');
                $table->integer('type_id')->comment('消息类型id');
                $table->string('title')->comment('消息标题');
                $table->text('content')->comment('消息内容');
                $table->string('redirect_url')->comment('消息点击跳转url');
                $table->tinyInteger('is_read')->default(0)->comment('读取状态，0未读，1已读');
                $table->text('msg_data')->comment('消息详情数据');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->nullable();
                $table->integer('read_at')->nullable()->comment('阅读时间');
                //定义索引
//                $table->index('uniacid');
                $table->index(['uniacid','type_id','created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_sys_msg_log', function (Blueprint $table) {
            //
        });
    }
}
