<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledYzGoodsSettingTable20220826 extends Migration
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
                if (!Schema::hasColumn('yz_goods_setting', 'scribing_show')) {
                    $table->tinyInteger('scribing_show')->default(0)->comment('原价划线显示：0显示划线1不显示划线');
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
