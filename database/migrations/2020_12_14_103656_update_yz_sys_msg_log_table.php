<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateYzSysMsgLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_sys_msg_log')) {
            Schema::table('yz_sys_msg_log', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_sys_msg_log', 'redirect_param')) {
                    $table->text('redirect_param')->nullable()->comment('消息跳转所需参数');
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
        Schema::table('yz_sys_msg_log', function (Blueprint $table) {
            if (Schema::hasColumn('yz_sys_msg_log', 'redirect_param')) {
                $table->dropColumn('redirect_param');
            }
        });
    }
}
