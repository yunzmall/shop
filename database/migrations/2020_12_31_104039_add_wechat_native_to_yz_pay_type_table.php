<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWechatNativeToYzPayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('yz_pay_type')) {
            if(!\app\common\models\PayType::find(57)) {
                \app\common\models\PayType::insert([
                    'id' => 57,
                    'name' => '微信扫码支付',
                    'plugin_id' => 0,
                    'code' => 'wechatNative',
                    'type' => 2,
                    'unit' => '分',
                    'group_id' => 1,
                    'setting_key' => 'shop.pay.weixin',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
            }

            if(\app\common\models\PayType::find(50)) {
                \app\common\models\PayType::where('id', 50)->update(['group_id'=>1]);
            }

            if(\app\common\models\PayType::find(55)) {
                \app\common\models\PayType::where('id', 55)->update(['group_id'=>1]);
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
        //
    }
}
