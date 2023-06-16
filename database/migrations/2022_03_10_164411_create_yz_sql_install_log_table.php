<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzSqlInstallLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_sql_install_log')) {
            Schema::create('yz_sql_install_log', function (Blueprint $table) {
                $table->integer('id',true);
                $table->string('path')->comment('安装绝对路径');
                $table->integer('created_at');
                $table->integer('updated_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_sql_install_log` comment '数据库安装记录'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
