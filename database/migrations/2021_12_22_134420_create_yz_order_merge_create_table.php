<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOrderMergeCreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_merge_create')) {
            Schema::create('yz_order_merge_create', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid');
                $table->text('order_ids')->comment('一同创建的订单ID，逗号拼接');
                $table->integer('created_at');
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_order_merge_create` comment '订单合并创建表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_merge_create');
    }
}
