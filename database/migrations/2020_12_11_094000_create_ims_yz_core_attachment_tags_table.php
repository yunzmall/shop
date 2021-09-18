<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzCoreAttachmentTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if(!Schema::hasTable('yz_core_attachment_tags')) {

            Schema::create('yz_core_attachment_tags', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('title')->nullable();
                $table->integer('uniacid')->nullable();
                $table->integer('source_type')->comment('1图片2音频3视频');
                $table->integer('tag_type')->comment('1系统分组2自定义分组');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
                $table->integer('sort')->nullable();
                $table->string('timeline')->nullable()->default(null);

            });
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ims_yz_core_attachment_tags');
	}

}
