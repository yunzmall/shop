<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsYzPluginsMeal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_plugins_meal')) {
            Schema::create('yz_plugins_meal', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('order_by')->comment('排序，数值越大排序越靠前');
                $table->string('name',255)->comment('套餐名称');
                $table->text('plugins')->comment('插件集合');
                $table->integer('state')->comment('是否显示');
                $table->integer('created_at');
                $table->integer('updated_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_plugins_meal` comment '插件套餐表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_plugins_meal');
    }
}
