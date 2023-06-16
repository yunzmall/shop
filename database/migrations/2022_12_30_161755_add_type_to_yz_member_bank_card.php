<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToYzMemberBankCard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_bank_card')) {
            Schema::table('yz_member_bank_card', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member_bank_card', 'type')) {
                    $table->boolean('type')->default(1)->comment('账户类型 1：对私,2：对公');
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
