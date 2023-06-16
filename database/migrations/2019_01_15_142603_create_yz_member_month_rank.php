<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberMonthRank extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_member_month_rank')) {
            Schema::create('yz_member_month_rank', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('member_id')->default(0)->comment('会员id');
                $table->smallInteger('year')->default(0)->comment('年');
                $table->smallInteger('month')->default(0)->comment('月');
                $table->decimal('price', 10)->default(0.00)->comment('一二级团队业绩总额');
                $table->integer('rank')->default(0)->comment('排行');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_month_rank` comment '会员--每月排行'");
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
    }
}
