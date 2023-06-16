<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledYzProtocolTable20220629 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_protocol')) {
            Schema::table('yz_protocol', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_protocol', 'default_tick')) {
                    $table->tinyInteger('default_tick')->default(0)->comment('注册协议默认勾选：1是0否');
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
