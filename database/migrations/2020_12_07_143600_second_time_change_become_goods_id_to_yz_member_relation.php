<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SecondTimeChangeBecomeGoodsIdToYzMemberRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_relation')) {
            Schema::table('yz_member_relation', function (Blueprint $table) {
                if (Schema::hasColumn('yz_member_relation', 'become_goods_id')) {
                    $table->text('become_goods_id')->nullable()->change();
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
        Schema::table('yz_member_relation', function (Blueprint $table) {
            if (Schema::hasColumn('yz_member_relation', 'become_goods_id')) {
                $table->string('become_goods_id')->nullable()->default(0)->change();
            }
        });
    }
}
