<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllterYzBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_balance', function (Blueprint $table) {
            $table->index(['member_id', 'uniacid'], "idx_member_id_uniacid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_balance', function (Blueprint $table) {
            $table->dropIndex('idx_member_id_uniacid');
        });
    }
}
