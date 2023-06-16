<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryDayToYzOrderAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_order_address', function (Blueprint $table) {
            //
            $table->string("delivery_day")->nullable()->comment("配送日期");
            $table->string("delivery_time")->nullable()->comment("配送时间");
        });
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
