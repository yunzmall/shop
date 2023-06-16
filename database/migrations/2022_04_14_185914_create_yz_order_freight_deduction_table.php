<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOrderFreightDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_freight_deduction')) {
            Schema::create('yz_order_freight_deduction',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('order_id')->nullable()->comment('订单ID')->index('order_idx');
                    $table->string('name', 150)->nullable()->comment('抵扣名称');
                    $table->string('code',50)->comment('抵扣标识');
                    $table->decimal('amount',14,2)->default(0.00)->comment('抵扣金额');
                    $table->decimal('coin',14,2)->default(0.00)->comment('抵扣数量');
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_freight_deduction` comment'订单--订单运费抵扣'");//表注释

        }

        if (Schema::hasTable('yz_order')) {
            Schema::table('yz_order', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order', 'initial_freight')) {
                    $table->decimal('initial_freight',10,2)->default(0.00)->nullable()->comment('订单初始运费金额');
                }
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
        Schema::dropIfExists('yz_order_freight_deduction');
    }
}
