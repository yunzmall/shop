<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOutsideAppSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_outside_app_setting')) {
            Schema::create('yz_outside_app_setting', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid')->index('uniacid_idx');
                $table->string('app_id')->nullable()->comment('应用AppID')->index('app_idx');
                $table->string('app_secret')->default('')->comment('应用secret');
                $table->text('black_list')->nullable()->comment('IP黑名单');
                $table->text('white_list')->nullable()->comment('IP白名单');
                $table->text('value')->nullable()->comment('其他设置');
                $table->tinyInteger('sign_required')->default(0)->comment('是否需要签名 0需要1不需要');
                $table->tinyInteger('is_open')->default(0)->comment('是否开启');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_outside_app_setting` comment '系统--对外应用设置表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_outside_app_setting');
    }
}
