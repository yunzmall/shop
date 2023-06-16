<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayTypeIdToYzPayOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_pay_order')) {
            Schema::table('yz_pay_order', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_pay_order', 'pay_type_id')) {
                    $table->integer('pay_type_id')->default(0)->comment('支付类型ID');
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
