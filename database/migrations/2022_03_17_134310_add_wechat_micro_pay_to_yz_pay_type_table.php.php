<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWechatMicroPayToYzPayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_pay_type')) {
            if (!\app\common\models\PayType::find(73)) {
                \app\common\models\PayType::insert([
                    'id' => 73,
                    'name' => '微信支付(付款码)',
                    'plugin_id' => 0,
                    'code' => 'wechatMicroPay',
                    'type' => 2,
                    'unit' => '元',
                    'group_id' => 1,
                    'setting_key' => 'shop.pay.weixin',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
            }
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
