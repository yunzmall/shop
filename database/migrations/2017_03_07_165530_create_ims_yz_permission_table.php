<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPermissionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_permission')) {
            Schema::create('yz_permission', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('type');
                $table->integer('item_id')->comment('操作员id');
                $table->string('permission')->comment('可操作权限');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_permission comment '商城--操作员权限表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_permission');
	}

}
