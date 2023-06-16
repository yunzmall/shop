<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRejectTimeToYzOrderRefund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_refund')) {
            Schema::table('yz_order_refund', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_refund', 'reject_time')) {
                    $table->integer('reject_time')->nullable()->comment('驳回时间');
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
