<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfirmPayToYzPayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!\app\common\models\PayType::find(54)) {

            \app\common\models\PayType::insert([
                'id' => 54,
                'name' => '确认',
                'plugin_id' => 0,
                'code' => 'confirmPay',
                'type' => 2,
                'unit' => '元',
                'group_id' => 0,
                'setting_key' => 'pay',
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
