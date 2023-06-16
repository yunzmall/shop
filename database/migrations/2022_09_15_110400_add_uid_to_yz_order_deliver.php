<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidToYzOrderDeliver extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_order_deliver', function (Blueprint $table) {
            //
            $table->integer("uid")->after("order_id")->comment("用户id");
            $table->index("uid", "idx_uid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_order_deliver', function (Blueprint $table) {
            //
            $table->dropIndex("idx_uid");
            $table->dropColumn("uid");
        });
    }
}
