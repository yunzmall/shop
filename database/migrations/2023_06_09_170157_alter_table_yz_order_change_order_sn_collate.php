<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableYzOrderChangeOrderSnCollate extends Migration
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
                if (Schema::hasColumn('yz_order', 'order_sn')) {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_order` CHANGE COLUMN `order_sn` `order_sn` VARCHAR(23) NOT NULL DEFAULT '' COMMENT '订单编号' COLLATE 'utf8mb4_unicode_ci'");
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
