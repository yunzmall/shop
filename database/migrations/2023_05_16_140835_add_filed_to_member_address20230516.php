<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledToMemberAddress20230516 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_address')) {
            Schema::table('yz_member_address', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member_address', 'position')) {
                    $table->string('position_address')->default('')->comment('位置地址');
                }
            });
        }
        if (Schema::hasTable('mc_member_address')) {
            Schema::table('mc_member_address', function (Blueprint $table) {
                if (!Schema::hasColumn('mc_member_address', 'position')) {
                    $table->string('position_address')->default('')->comment('位置地址');
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
