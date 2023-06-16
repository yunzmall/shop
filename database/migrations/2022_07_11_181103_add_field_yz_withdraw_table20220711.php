<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldYzWithdrawTable20220711 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_withdraw')) {
            Schema::table('yz_withdraw', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_withdraw', 'reject_reason')) {
                    $table->string('reject_reason')->nullable()->comment('驳回原因');
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
