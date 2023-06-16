<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPageShareRecordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if(!Schema::hasTable('yz_page_share_record')) {
            Schema::create('yz_page_share_record', function (Blueprint $table) {
                $table->increments('id')->comment('主键id');
                $table->integer('uniacid')->default(0);
                $table->integer('member_id')->nullable()->index('memberid')->comment('分享者ID');
                $table->text('share_url', 65535)->nullable()->comment('分享链接');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_page_share_record comment '商城--页面分享记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ims_yz_page_share_record');
	}

}
