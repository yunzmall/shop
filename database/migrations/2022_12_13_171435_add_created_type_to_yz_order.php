<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedTypeToYzOrder extends Migration
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
                if (!Schema::hasColumn('yz_order', 'created_type')) {
                    $table->integer('created_type')->default(0)->comment('订单创建类型 0线上订单 1线下订单(pos收银机)');
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
        //
    }
}
