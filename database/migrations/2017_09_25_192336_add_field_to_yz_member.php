<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToYzMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Schema::hasTable('yz_member')) {

            if (!Schema::hasColumn('yz_member', 'pay_password')) {
                Schema::table('yz_member', function (Blueprint $table) {
                    $table->string('pay_password','45')->comment('	商城支付密码');
                });
            }
            if (!Schema::hasColumn('yz_member', 'salt')) {
                Schema::table('yz_member', function (Blueprint $table) {
                    $table->string('salt','8')->comment('加密随机码');
                });
            }
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
