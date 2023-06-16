<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYzMemberLevelTable20211227 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_level')) {
            Schema::table('yz_member_level', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member_level', 'give_integral')) {
                    $table->decimal('give_integral', 12,2)->nullable()->comment('购买商品赠送消费积分比例');
                }
                if (!Schema::hasColumn('yz_member_level', 'give_point_today')) {
                    $table->integer('give_point_today')->nullable()->comment('每天最高可获积分数量');
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
        if (Schema::hasTable('yz_member_level')) {
            Schema::table('yz_member_level', function (Blueprint $table) {
                if (Schema::hasColumn('yz_member_level', 'give_integral')) {
                    $table->dropColumn('give_integral');
                }
                if (Schema::hasColumn('yz_member_level', 'give_point_today')) {
                    $table->dropColumn('give_point_today');
                }
            });
        }
    }
}
