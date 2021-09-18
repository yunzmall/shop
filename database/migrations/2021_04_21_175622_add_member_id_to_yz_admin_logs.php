<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberIdToYzAdminLogs extends Migration
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
                if (!Schema::hasColumn('yz_admin_logs','member_id')) {
                    $table->integer('member_id')->default(0)->after('ip')->comment('关联的会员id（实时记录）');
                }

                if (!Schema::hasColumn('yz_admin_logs','username')) {
                    $table->string('username',100)->default('')->nullable()->after('ip')->comment('登录账号');
                }

                if (!Schema::hasColumn('yz_admin_logs','deleted_at')) {
                    $table->integer('deleted_at')->nullable();
                }
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_admin_logs` comment '管理员登录日志表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_admin_logs', function (Blueprint $table) {
            $table->dropColumn('member_id');
            $table->dropColumn('username');
            $table->dropColumn('deleted_at');
        });
    }
}
