<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderPayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_pay')) {
            Schema::create('yz_order_pay', function (Blueprint $table) {
                $table->increments('id');
                $table->string('pay_sn', 23)->default('')->comment('支付流水号');
                $table->boolean('status')->default(0)->comment('状态');
                $table->boolean('pay_type_id')->default(0)->comment('支付类型ID');
                $table->integer('pay_time')->default(0)->comment('支付时间');
                $table->integer('refund_time')->default(0);
                $table->string('order_ids', 500)->default('')->comment('合并支付订单ID数组');
                $table->decimal('amount', 10)->default(0.00)->comment('支付金额');
                $table->integer('uid');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_pay` comment'订单--支付流水记录'");//表注释

        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_pay');
    }

}
