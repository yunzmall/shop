<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBalanceToYzDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_deduction')) {
            \Illuminate\Support\Facades\DB::table('yz_deduction')->insert(['code' => 'balance', 'enable'=> 1, 'created_at'=>time(), 'update_at'=>time(), 'deleted_at'=> null]);
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
