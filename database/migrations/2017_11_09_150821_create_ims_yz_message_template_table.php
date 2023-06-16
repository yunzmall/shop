<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMessageTemplateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_message_template')) {
            Schema::create('yz_message_template', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->string('title')->default('0')->comment('模板标题');
                $table->string('template_id')->default('')->comment('模板微信ID');
                $table->text('first', 65535)->nullable()->comment('模板名称');
                $table->string('first_color')->nullable()->comment('模板名称字体颜色');
                $table->text('data', 65535)->nullable()->comment('内容');
                $table->text('remark', 65535)->nullable()->comment('模板备注');
                $table->string('remark_color')->nullable()->comment('模板备注字体颜色');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_message_template comment '微信公众号信息模板表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('yz_message_template');
	}

}
