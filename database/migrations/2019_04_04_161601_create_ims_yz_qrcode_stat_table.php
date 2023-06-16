<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzQrcodeStatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_qrcode_stat')) {
            Schema::create('yz_qrcode_stat', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->integer('acid')->default(0)->nullable();
                $table->integer('qid')->default(0)->nullable()->comment('二维码id');
                $table->string('openid')->default('')->nullable()->comment('微信openid');
                $table->integer('type')->default(0)->nullable()->comment('类型');
                $table->integer('qrcid')->default(0)->nullable()->comment('场景值');
                $table->string('scene_str')->default('')->nullable()->comment('用来存储营关联的销码日志ID,营销码日志表不做一对多. 定时任务自动清理30天之后的记录');
                $table->string('name')->default('')->nullable()->comment('用来定时清理营销码记录的标识');
                $table->integer('createtime')->default(0)->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_qrcode_stat comment '商城--扫码记录表表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_qrcode_stat')) {
            Schema::dropIfExists('yz_qrcode_stat');
        }
    }
}
