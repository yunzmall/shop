<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMenuTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_menu')) {
            Schema::create('yz_menu', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('name', 45)->comment('菜单名字');
                $table->string('item', 45)->comment('菜单标识');
                $table->string('url')->default('')->comment('菜单url');
                $table->string('url_params')->default('')->comment('菜单url参数');
                $table->boolean('permit')->default(0)->comment('权限控制');
                $table->boolean('menu')->default(0)->comment('菜单显示');
                $table->string('icon', 45)->default('')->comment('菜单图标');
                $table->integer('parent_id')->default(0)->comment('父级菜单ID');
                $table->integer('sort')->default(0)->comment('菜单排序');
                $table->boolean('status')->default(0)->comment('菜单状态1开启，0未开启');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_menu comment '菜单表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_menu');
	}

}
