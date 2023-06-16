<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsYzPluginsMealPlatform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_plugins_meal_platform')) {
            Schema::create('yz_plugins_meal_platform', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid');
                $table->integer('plugins_meal_id');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_plugins_meal_platform` comment '插件套餐使用记录表'");
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_plugins_meal_platform');
    }
}
