<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzSysMsgTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_sys_msg_type')) {
            Schema::create('yz_sys_msg_type', function (Blueprint $table) {
                //系统消息类型
                $table->increments('id');
//                $table->integer('uniacid');类型通用
                $table->string('type_name')->comment('类型名称');
                $table->string('icon_src')->nullable()->comment('类型图标地址');
            });

            \Illuminate\Support\Facades\DB::table('yz_sys_msg_type')->insert(array (
                0 =>
                    array (
                        'id' => 1,
                        'type_name' => '系统通知',
                        'icon_src' => 'icon-ht_content_systemmessage',
                    ),
                1 =>
                    array (
                        'id' => 2,
                        'type_name' => '订单通知',
                        'icon_src' => 'icon-ht_content_order',
                    ),
                2 =>
                    array (
                        'id' => 3,
                        'type_name' => '提现通知',
                        'icon_src' => 'icon-ht_content_tixian',
                    ),
                3 =>
                    array (
                        'id' => 4,
                        'type_name' => '申请通知',
                        'icon_src' => 'icon-ht_content_apply',
                    ),
                4 =>
                    array (
                        'id' => 5,
                        'type_name' => '商品库存',
                        'icon_src' => 'icon-ht_content_goods',
                    ),
                5 =>
                    array (
                        'id' => 6,
                        'type_name' => '优惠券',
                        'icon_src' => 'icon-ht_content_coupons',
                    ),
            ));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_sys_msg_type', function (Blueprint $table) {
            //
        });
    }
}
