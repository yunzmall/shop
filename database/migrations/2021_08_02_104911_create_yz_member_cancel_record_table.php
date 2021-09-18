<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberCancelRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('yz_member_cancel_record')) {
			Schema::create('yz_member_cancel_record', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('uniacid')->default(0)->nullable();
				$table->integer('member_id')->default(0)->nullable();
				$table->integer('status')->default(1)->comment('审核状态');
                $table->integer('created_at')->nullable();
				$table->integer('updated_at')->nullable();
				$table->integer('deleted_at')->nullable();
			});
		}
		\Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_member_cancel_record comment '账号注销审核记录'");

	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_member_cancel_record');
    }
}
