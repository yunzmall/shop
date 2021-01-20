<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderRefundChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_refund_change_log')) {
            Schema::create('yz_order_refund_change_log', function (Blueprint $table) {
                $table->integer('id', true)->comment('主键ID');
                $table->integer('order_id')->nullable()->comment('订单ID');
                $table->integer('refund_id')->nullable()->comment('退款记录id');
                $table->decimal('old_price',14,2)->nullable()->default(0)->comment('旧金额');
                $table->decimal('new_price',14,2)->nullable()->default(0)->comment('新金额');
                $table->decimal('change_price',14,2)->nullable()->default(0)->comment('改价金额');
                $table->string('username',100)->default('')->comment('操作员名称');
                $table->integer('created_at')->nullable()->comment('创建时间');
                $table->integer('updated_at')->nullable()->comment('修改时间');
                $table->integer('deleted_at')->nullable()->comment('删除时间');
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_refund_change_log` comment '订单--退款金额修改记录'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_refund_change_log');
    }
}
