<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToYzCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Schema::hasTable('yz_coupon')) {
            Schema::table('yz_coupon', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_coupon', 'content')) {
                    $table->longText('content')->nullable()->comment('说明内容');
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
