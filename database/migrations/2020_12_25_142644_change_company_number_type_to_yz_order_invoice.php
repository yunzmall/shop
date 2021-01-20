<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCompanyNumberTypeToYzOrderInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_invoice')) {
            if (Schema::hasColumn('yz_order_invoice', 'company_number')) {
                Schema::table('yz_order_invoice', function (Blueprint $table) {
                    $table->string('company_number')->nullable()->comment('单位识别码')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_order_invoice')) {
            if (Schema::hasColumn('yz_order_invoice', 'company_number')) {
                Schema::table('yz_order_invoice', function (Blueprint $table) {
                    $table->integer('company_number')->nullable()->comment('	单位识别码')->change();
                });
            }
        }
    }
}
