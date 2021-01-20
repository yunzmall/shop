<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateIdEqual4YzDispatchType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_dispatch_type')) {
            $dispatchType = \app\common\models\DispatchType::find(4);
            if ($dispatchType->code == 'store_deliver' && $dispatchType->plugin == 33) {
                $dispatchType->fill(['code'=> 'hotel_deliver']);
                $dispatchType->save();
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
