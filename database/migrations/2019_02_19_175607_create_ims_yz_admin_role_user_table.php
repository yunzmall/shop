<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzAdminRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_admin_role_user')) {
            Schema::create('yz_admin_role_user', function (Blueprint $table) {
                $table->integer('role_id');
                $table->integer('user_id');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_admin_role_user comment '角色管理-管理员用户与角色中间表'");//表注释

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_admin_role_user');
    }
}
