<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOutsideOrderHasManyOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('yz_outside_order_has_many_order')) {
            Schema::create('yz_outside_order_has_many_order', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('outside_order_id')->comment('外部订单记录id');
                $table->integer('order_id')->nullable()->comment('商城订单id');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_outside_order_has_many_order` comment '订单与第三方订单关系表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_outside_order_has_many_order');
    }
}
