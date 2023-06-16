<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzWechatQrcodeFansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_wechat_qrcode_fans')) {
            Schema::create('yz_wechat_qrcode_fans', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('member_id')->index('idx_member_id')->comment('会员id');
                $table->string('openid', 50)->comment('微信openid');
                $table->string('nickname', 20)->comment('昵称');
                $table->boolean('gender')->default(0)->comment('性别，0女，1男');
                $table->string('avatar')->comment('头像');
                $table->string('province', 4)->comment('省');
                $table->string('city', 25)->comment('市');
                $table->string('country', 10)->comment('区');
                $table->integer('created_at')->unsigned()->default(0);
                $table->integer('updated_at')->unsigned()->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_wechat_qrcode_fans` comment'会员--会员粉丝'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_wechat_qrcode_fans');
    }
}
