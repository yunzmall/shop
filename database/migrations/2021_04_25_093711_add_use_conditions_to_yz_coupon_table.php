<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUseConditionsToYzCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_coupon')) {
            Schema::table('yz_coupon', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_coupon','use_conditions')) {
                    $table->longText('use_conditions')->nullable()->comment('使用条件');
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

    }
}
