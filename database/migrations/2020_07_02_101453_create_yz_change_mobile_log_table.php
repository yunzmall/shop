<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzChangeMobileLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_change_mobile_log')) {
            Schema::create('yz_change_mobile_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号');
                $table->integer('member_id')->comment('会员id');
                $table->string('mobile_before', 18)->default(0)->comment('修改前手机号');
                $table->string('mobile_after', 18)->default(0)->comment('修改后手机号');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
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
        Schema::dropIfExists('yz_change_mobile_log');
    }
}
