<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzRichTextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_rich_text')) {
            Schema::create('yz_rich_text', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->string('group')->comment('组');
                $table->string('key')->comment('键');
                $table->longText('value')->nullable()->comment('富文本');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_rich_text comment '富文本存储表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_rich_text');
    }
}
