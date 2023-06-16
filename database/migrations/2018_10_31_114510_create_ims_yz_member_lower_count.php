<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzMemberLowerCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('yz_member_lower_count')) {
            Schema::create('yz_member_lower_count', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0);
                $table->integer('uniacid')->default(0);
                $table->integer('first_total')->default(0)->comment('一级下线总数');
                $table->integer('second_total')->default(0)->comment('二级下线总数');
                $table->integer('third_total')->default(0)->comment('三级下线总数');
                $table->integer('team_total')->default(0)->comment('团队总数');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_member_lower_count comment '会员下线统计表表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('yz_member_lower_count');
    }
}
