<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixOrderGoodsCoupon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_goods_coupon')) {
            $records = \app\common\models\coupon\OrderGoodsCoupon::where(['send_type' => \app\common\models\coupon\OrderGoodsCoupon::MONTH_TYPE, 'status' => 1])
                ->get();
            foreach ($records as $record) {
                if ($record->send_num > $record->end_send_num) {
                    $end = $record->end_send_num + 1;
                    $record->end_send_num = $end;
                    if ($end < $record->send_num) {
                        $record->status = 0;
                    }
                    $record->save();
                }
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
