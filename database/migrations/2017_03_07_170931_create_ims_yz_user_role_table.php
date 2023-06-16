<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzUserRoleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_user_role')) {
            Schema::create('yz_user_role', function (Blueprint $table) {
                $table->integer('user_id')->comment('操作员id');
                $table->integer('role_id')->comment('角色id');
                $table->primary(['user_id', 'role_id']);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_user_role comment '商城--角色与操作员中间表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_user_role');
	}

}
