<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzIncomeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_income_log')) {
            Schema::create('yz_income_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('income_id')->comment('收入表id');
                $table->text('before')->nullable()->comment('修改前内容');
                $table->text('after')->nullable()->comment('修改后内容');
                $table->string('remark')->nullable()->comment('备注');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_income_log comment '会员收入修改记录表'");//表注释
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
