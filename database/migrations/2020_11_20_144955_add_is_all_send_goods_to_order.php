<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAllSendGoodsToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order')) {
            Schema::table('yz_order', function (Blueprint $table) {
                //
                if (!Schema::hasColumn('yz_order','is_all_send_goods')) {
                    $table->tinyInteger('is_all_send_goods')->nullable()->default(0)->comment('0 正常全部发货 1部分发货 2多包裹全部发货');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_order', function (Blueprint $table) {
            $table->dropColumn('is_all_send_goods');
        });
    }
}
