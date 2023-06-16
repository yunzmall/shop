<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzWechatRuleKeywordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_wechat_rule_keyword')) {
            Schema::create('yz_wechat_rule_keyword', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('rid')->default(0)->comment('规则id');
                $table->integer('uniacid')->default(0);
                $table->string('module')->default('')->nullable()->comment('回复模块');
                $table->string('content')->default('')->nullable()->comment('关键字内容');
                $table->integer('type')->default(0)->nullable()->comment('关键字匹配类型');
                $table->integer('displayorder')->default(0)->nullable()->comment('优先级');
                $table->integer('status')->default(0)->nullable()->comment('是否开启，1开启，0关闭');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
                
                
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_wechat_rule_keyword comment '商城--公共号回复关键字表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_wechat_rule_keyword');
    }
}
