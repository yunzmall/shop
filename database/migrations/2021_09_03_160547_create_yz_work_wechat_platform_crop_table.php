<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzWorkWechatPlatformCropTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_work_wechat_platform_crop')) {
            Schema::create('yz_work_wechat_platform_crop', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->integer('uid')->default(0)->comment('管理员uid');
                $table->string('name',500)->default('')->comment('名称');
                $table->string('logo_img',500)->default('logo');
                $table->integer('status')->default(1)->comment('状态：1正常，-1停用');
                $table->integer('member_uid')->default(0)->comment('会员uid');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_work_wechat_platform_crop` comment '企业微信--企业应用表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_work_wechat_platform_crop');
    }
}
