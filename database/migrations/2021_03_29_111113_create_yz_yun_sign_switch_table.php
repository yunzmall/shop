<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzYunSignSwitchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_yun_sign_switch')) {
            Schema::create('yz_yun_sign_switch', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->tinyInteger('switch_platform')->default(0)->nullable()->comment('开关');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_yun_sign_switch comment '平台开关设置表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_yun_sign_switch');
    }
}
