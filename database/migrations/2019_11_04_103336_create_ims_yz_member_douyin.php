<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzMemberDouyin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('yz_member_douyin')) {
            Schema::create('yz_member_douyin', function (Blueprint $table) {
                $table->integer('douyin_id', true);
                $table->integer('uniacid');
                $table->integer('member_id')->comment('登录会员ID');
                $table->string('openid', 50)->comment('抖音openid');
                $table->string('nickname', 20)->comment('昵称');
                $table->string('avatar')->comment('头像');
                $table->boolean('gender')->comment('性别0未知1男2女');
                $table->integer('created_at')->unsigned()->default(0);
                $table->integer('updated_at')->unsigned()->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_member_douyin comment '抖音登录记录表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('yz_member_douyin');
    }
}
