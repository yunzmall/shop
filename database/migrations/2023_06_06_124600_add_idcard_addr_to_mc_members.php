<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdcardAddrToMcMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('mc_members')) {
            Schema::table('mc_members', function (Blueprint $table) {
                if (!Schema::hasColumn('mc_members', 'idcard_addr')) {
                    $table->text('idcard_addr')->default('')->comment('身份证地址');
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
        Schema::table('mc_members', function (Blueprint $table) {
            //
        });
    }
}
