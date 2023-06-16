<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzWechatRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if (!Schema::hasTable('yz_wechat_rule')) {
            Schema::create('yz_wechat_rule', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->string('name')->default('')->nullable()->comment('规则名称');
                $table->string('module')->default('')->nullable()->comment('回复模块');
                $table->integer('displayorder')->default(0)->nullable()->comment('优先级');
                $table->integer('status')->default(0)->nullable()->comment('是否开启，1开启，0关闭');
                $table->string('containtype')->default('')->nullable()->comment('回复内容类型');
                $table->integer('reply_type')->default(0)->nullable()->comment('回复类型');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();

                
            });
           \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
               . "yz_wechat_rule comment '商城--公共号回复规则表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_wechat_rule');
    }
}
