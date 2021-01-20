<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUidToMemberCoupon extends Migration
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
                if (Schema::hasColumn('yz_member_coupon', 'uid')) {
                    $table->integer('uid')->default(0)->change();
                }
                if (!Schema::hasColumn('yz_member_coupon', 'is_expired')) {
                    $table->integer('is_expired')->default(0)->index('idx_isexpired');
                }
            });
        }
        try {
            if (Schema::hasTable('yz_member_coupon')) {
                Schema::table('yz_member_coupon', function (Blueprint $table) {
                    $table->index('uid');
                });
            }
        } catch (\app\common\exceptions\ShopException $e) {

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
