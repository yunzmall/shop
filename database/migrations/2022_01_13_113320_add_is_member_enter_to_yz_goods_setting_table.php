<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMemberEnterToYzGoodsSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_setting')) {
            Schema::table('yz_goods_setting', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_setting', 'is_member_enter')) {
                    $table->tinyInteger('is_member_enter')->default(1)->comment('会员中心开关：1-开启，0-关闭');
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
        Schema::table('yz_goods_setting', function (Blueprint $table) {
            //
        });
    }
}
