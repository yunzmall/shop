<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzAdminRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_admin_roles')) {
            Schema::create('yz_admin_roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->comment('角色名称');
                $table->string('description')->comment('备注');
                $table->timestamps();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_admin_roles comment '角色管理-角色表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_admin_roles');
    }
}
