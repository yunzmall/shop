<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzWithdrawRichText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_withdraw_rich_text')) {
            Schema::create('yz_withdraw_rich_text', function (Blueprint $table) {
                $table->increments('id')->unique();
                $table->integer('uniacid');
                $table->text('content')->nullable()->comment('内容');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
        }
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_withdraw_rich_text comment '提现说明富文本'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
