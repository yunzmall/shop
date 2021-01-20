<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToYzMemberCoupon extends Migration
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
                if (!Schema::hasColumn('yz_member_coupon', 'expired_at')) { 
                    $table->string('expired_at', 100)->nullable()->default('');
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
        Schema::table('yz_member_coupon', function (Blueprint $table) {

            $table->dropColumn('expired_at');
        });
    }
}
