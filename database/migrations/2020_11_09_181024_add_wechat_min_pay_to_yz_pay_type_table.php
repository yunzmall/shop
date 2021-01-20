<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWechatMinPayToYzPayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!\app\common\models\PayType::find(55)) {

            \app\common\models\PayType::insert([
                'id' => 55,
                'name' => '微信小程序支付',
                'plugin_id' => 0,
                'code' => 'wechatMinPay',
                'type' => 2,
                'unit' => '元',
                'group_id' => 1,
                'setting_key' => 'plugin.min_app',
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
