<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzSiteSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_site_setting')) {
            Schema::create('yz_site_setting', function (Blueprint $table) {
                $table->increments('id');
                $table->mediumText('value')->default('')->comment('设置内容');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_site_setting` comment '系统工具--系统工具设置表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_site_setting');
    }
}
