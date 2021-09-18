<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTopToYzUniacidAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('yz_uniacid_app', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('yz_uniacid_app', 'is_top')) {
                $table->tinyInteger('is_top')->nullable()->default(0)->comment('0不置顶， 1置顶');
            }

            if (!Schema::hasColumn('yz_uniacid_app', 'topped_at')) {
                $table->integer('topped_at')->nullable()->default(0)->comment('置顶时间');
            }

            if (!Schema::hasColumn('yz_uniacid_app', 'admin_is_top')) {
                $table->tinyInteger('admin_is_top')->nullable()->default(0)->comment('0不置顶， 1超级管理员置顶');
            }

            if (!Schema::hasColumn('yz_uniacid_app', 'admin_topped_at')) {
                $table->integer('admin_topped_at')->nullable()->default(0)->comment('超级管理员置顶时间');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('yz_uniacid_app', function (Blueprint $table) {
            //
            $table->dropColumn('is_top');
            $table->dropColumn('topped_at');
            $table->dropColumn('admin_is_top');
            $table->dropColumn('admin_topped_at');
        });
    }
}
