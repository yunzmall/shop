<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzSystemWhiteListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_system_white_list')) {
            Schema::create('yz_system_white_list', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('ip')->comment('ip地址');
                $table->boolean('is_open')->default(0)->comment('是否启用:0否1启用');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_system_white_list` comment '独立后台登录白名单表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_system_white_list');
    }
}
