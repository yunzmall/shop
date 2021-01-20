<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIsAllSendGoodsToImsYzOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order')) {
            if (Schema::hasColumn('yz_order', 'is_all_send_goods')) {
                Schema::table('yz_order', function (Blueprint $table) {
                    $table->Integer('is_all_send_goods')->nullable()->default(0)->change();
                });
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
