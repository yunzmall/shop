<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableImsYzMemberLevelUpdateColumnGoodsId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Schema::hasTable('yz_member_level')) {
            Schema::table('yz_member_level', function (Blueprint $table) {
                if (Schema::hasColumn('yz_member_level', 'goods_id')) {
                    $table->string('goods_id',2000)->change();
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
        Schema::table('yz_member_level', function (Blueprint $table) {
            if (Schema::hasColumn('yz_member_level', 'goods_id')) {
                $table->string('goods_id',255)->change();
            }
        });
    }
}
