<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderAddressUpdateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_address_update_log')) {
            Schema::create('yz_order_address_update_log', function (Blueprint $table) {
                $table->integer('id', true)->comment('主键ID');
                $table->integer('uniacid')->default(0)->comment('平台ID');
                $table->integer('order_id')->nullable()->comment('订单ID');
                $table->integer('user_id')->nullable()->comment('操作员id');
                $table->integer('province_id')->nullable()->default(0)->comment('省ID');
                $table->integer('city_id')->nullable()->default(0)->comment('市ID');
                $table->integer('district_id')->nullable()->default(0)->comment('区ID');
                $table->integer('street_id')->nullable()->default(0)->comment('街道ID');
                $table->string('realname')->nullable()->comment('收件人姓名');
                $table->string('phone')->nullable()->comment('联系方式');
                $table->string('new_address')->nullable()->comment('新地址');
                $table->string('old_address')->nullable()->comment('旧地址');
                $table->string('old_name')->nullable()->comment('旧名称');
                $table->string('old_phone')->nullable()->comment('旧手机号');
                $table->integer('created_at')->nullable()->comment('创建时间');
                $table->integer('updated_at')->nullable()->comment('修改时间');
                $table->integer('deleted_at')->nullable()->comment('删除时间');
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_address_update_log` comment '订单--订单地址修改记录'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_address_update_log');
    }
}
