<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWechatTradePayToYzPayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_pay_type')) {
            if (!\app\common\models\PayType::find(92)) {
                \app\common\models\PayType::insert([
                    'id' => 92,
                    'name' => '微信支付(视频号)',
                    'plugin_id' => 0,
                    'code' => 'wechatTradePay',
                    'type' => 2,
                    'unit' => '元',
                    'group_id' => 1,
                    'setting_key' => '',
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
