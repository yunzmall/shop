<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipInfomattionLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_membership_infomattion_log')) {
            Schema::create('yz_membership_infomattion_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid');
                $table->integer('uid');
                $table->string('old_data')->nullable()->comment('用户修改前信息');
                $table->string('session_id')->nullable()->comment('session_id');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_membership_infomattion_log comment '会员信息修改记录表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_membership_infomattion_log');
    }
}
