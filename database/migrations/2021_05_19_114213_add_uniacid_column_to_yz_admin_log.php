<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniacidColumnToYzAdminLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_admin_logs')) {
            Schema::table('yz_admin_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_admin_logs','uniacid')) {
                    $table->integer('uniacid')->after('id')->default(0);
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
        Schema::table('yz_admin_logs', function (Blueprint $table) {
            $table->dropColumn('uniacid');
        });
    }
}
