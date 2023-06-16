<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_group')) {
            Schema::create('yz_member_group', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->comment('公众号');
                $table->string('group_name', 45)->comment('分组名称');
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_group` comment '会员--分组'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_group');
	}

}
