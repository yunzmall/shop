<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNearExpirationToMemberCoupon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_coupon')) {
            Schema::table('yz_member_coupon', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member_coupon', 'near_expiration')) {
                    $table->tinyInteger('near_expiration')->default(0)->comment('即将过期');
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
