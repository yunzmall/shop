<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzQrcodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_qrcode')) {
            Schema::create('yz_qrcode', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->integer('acid')->default(0)->nullable();
                $table->string('type')->default('')->nullable();
                $table->integer('extra')->default(0)->nullable();
                $table->integer('qrcid')->default(0)->nullable()->comment('场景值');
                $table->string('scene_str')->default('')->nullable()->comment('用来存储营关联的销码日志ID,营销码日志表不做一对多. 定时任务自动清理30天之后的记录');
                $table->string('name')->default('')->nullable()->comment('用来定时清理营销码记录的标识');
                $table->string('keyword')->default('')->nullable()->comment('关键字');
                $table->integer('model')->default(0)->nullable()->comment('1临时二维码，2永久二维码');
                $table->string('ticket')->default('')->nullable()->comment('微信返回的临时票据，用于在获取授权链接时作为参数传入');
                $table->string('url')->default('')->nullable()->comment('微信返回的地址');
                $table->integer('expire')->default(0)->nullable()->comment('期限');
                $table->integer('subnum')->default(0)->nullable();
                $table->integer('createtime')->default(0)->nullable();
                $table->integer('status')->default(0)->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_qrcode comment '商城--微擎框架的二维码数据表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_qrcode')) {
            Schema::dropIfExists('yz_qrcode');
        }
    }
}
