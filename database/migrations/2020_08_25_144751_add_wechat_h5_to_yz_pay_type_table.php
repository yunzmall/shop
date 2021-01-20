<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWechatH5ToYzPayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!\app\common\models\PayType::find(50)) {

            \app\common\models\PayType::insert([
                'id' => 50,
                'name' => '微信H5',
                'plugin_id' => 0,
                'code' => 'wechatH5',
                'type' => 2,
                'unit' => '元',
                'group_id' => 0,
                'setting_key' => 'shop.pay.weixin',
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
