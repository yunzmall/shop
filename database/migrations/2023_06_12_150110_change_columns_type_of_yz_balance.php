<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsTypeOfYzBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_balance')) {
            Schema::table('yz_balance', function (Blueprint $table) {
                if (Schema::hasColumn('yz_balance', 'type')) {
                    $table->integer('type')->comment('变动方式，1收入，2支出')->change();
                }
                if (Schema::hasColumn('yz_balance', 'service_type')) {
                    $table->integer('service_type')->comment('变动类型')->change();
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
