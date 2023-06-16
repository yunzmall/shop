<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MinAppTemplateMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_mini_app_template_message')) {
            Schema::create('yz_mini_app_template_message', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->string('title')->comment('模板标题');
                $table->string('template_id', 45)->comment('微信模板ID');
                $table->text('data', 65535)->nullable()->comment('模板参数');
                $table->integer('is_default')->nullable()->comment('是否显示：1是，0否');
                $table->integer('is_open')->default(0)->comment('是否开启哦：1是，0否');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_mini_app_template_message comment '微信小程序消息模板表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_mini_app_template_message');
    }
}
