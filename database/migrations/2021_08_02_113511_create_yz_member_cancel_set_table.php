<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberCancelSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('yz_member_cancel_set')) {
			Schema::create('yz_member_cancel_set', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('uniacid')->default(0)->nullable();
				$table->tinyInteger('status')->default(1)->nullable();
				$table->tinyInteger('tel_status')->default(1)->nullable();
				$table->string('title')->nullable()->comment('标题');
				$table->text('content')->nullable()->comment('内容');
                $table->integer('created_at')->nullable();
				$table->integer('updated_at')->nullable();
				$table->integer('deleted_at')->nullable();
			});
		}
		\Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_member_cancel_set comment '账号注销审核设置'");

	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_member_cancel_set');
    }
}
