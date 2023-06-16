<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToYzOrderRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (\Schema::hasTable('yz_order_refund')) {
            Schema::table('yz_order_refund', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_refund', 'freight_price')) {
                    $table->decimal('freight_price',10,2)->default(0.00)->nullable()->comment('运费金额');
                }

                if (!Schema::hasColumn('yz_order_refund', 'other_price')) {
                    $table->decimal('other_price',10,2)->default(0.00)->nullable()->comment('其他费用金额');
                }
            });

        }

        if (\Schema::hasTable('yz_order_refund_change_log')) {
            Schema::table('yz_order_refund_change_log', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_refund_change_log', 'change_freight_price')) {
                    $table->decimal('change_freight_price',10,2)->default(0.00)->nullable()->comment('修改运费金额');
                }

                if (!Schema::hasColumn('yz_order_refund_change_log', 'change_other_price')) {
                    $table->decimal('change_other_price',10,2)->default(0.00)->nullable()->comment('修改其他费用金额');
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
