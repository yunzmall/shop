<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzSmsSendLimitTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_sms_send_limit')) {
            Schema::create('yz_sms_send_limit', function (Blueprint $table) {
                $table->integer('sms_id', true);
                $table->integer('uniacid');
                $table->string('mobile', 11)->comment('短信接受电话');
                $table->boolean('total')->comment('短信条数');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()
                ."yz_sms_send_limit` comment '短信--短信发送限制表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_sms_send_limit');
	}

}
