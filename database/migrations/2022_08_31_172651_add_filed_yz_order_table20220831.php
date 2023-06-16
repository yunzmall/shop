<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledYzOrderTable20220831 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order')) {
            Schema::table('yz_order', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order', 'tax_fee')) {
                    $table->decimal('tax_fee',11,2)->default(0)->comment('税费优惠，负数优惠，正数加钱');
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
