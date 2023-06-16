<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderPackage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_package')) {
            Schema::create('yz_order_package', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('order_id')->comment('订单表id');
                $table->integer('order_goods_id')->comment('订单商品表id');
                $table->integer('total')->comment('数量');
                $table->integer('order_express_id')->comment('订单物流表id');
                $table->integer('created_at');
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();

                $table->index(['order_id','order_goods_id'],'ids_Oid_OGid');
                $table->index('order_express_id','ids_OEid');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_order_package` comment '订单包裹表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
