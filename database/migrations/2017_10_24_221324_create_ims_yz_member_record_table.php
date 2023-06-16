<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzMemberRecordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_record')) {
            Schema::create('yz_member_record', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('所属公众号');
                $table->integer('uid')->comment('会员ID');
                $table->integer('parent_id')->comment('会员上级ID');
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_record` comment '会员--记录表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasTable('yz_member_record')) {

            Schema::drop('yz_member_record');
        }
	}

}
