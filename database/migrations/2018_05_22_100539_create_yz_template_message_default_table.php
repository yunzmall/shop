<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzTemplateMessageDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_template_message_default')) {
            Schema::create('yz_template_message_default', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->string('template_id')->nullable()->comment('模板id');
                $table->string('template_id_short')->nullable()->comment('模板编号');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_template_message_default comment '微信模板消息--微信模板记录表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_template_message_default');
    }
}
