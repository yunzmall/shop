<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderExpeditingDelivery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_expediting_delivery')) {
            Schema::create('yz_order_expediting_delivery', function (Blueprint $table) {
                $table->integer('id', true)->comment('主键ID');
                $table->integer('uniacid')->comment('平台ID');
                $table->integer('order_id')->nullable()->comment('订单ID');
                $table->string('order_sn')->nullable()->comment('订单编号');
                $table->integer('created_at')->nullable()->comment('创建时间');
                $table->integer('updated_at')->nullable()->comment('修改时间');
                $table->integer('deleted_at')->nullable()->comment('删除时间');
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
        Schema::dropIfExists('yz_order_expediting_delivery');
    }
}
