<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeLimitToYzGoodsPrivilegeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_privilege')) {
            if (!Schema::hasColumn('yz_goods_privilege', 'buy_limit_status')) {
                Schema::table('yz_goods_privilege', function (Blueprint $table) {
                    $table->boolean('buy_limit_status')->default(0)->comment('限购时段状态,1-开启');
                });
            }
            if (!Schema::hasColumn('yz_goods_privilege', 'time_limits')) {
                Schema::table('yz_goods_privilege', function (Blueprint $table) {
                    $table->text('time_limits')->nullable()->comment('限购时段时间段和数量');
                });
            }
            if (!Schema::hasColumn('yz_goods_privilege', 'buy_limit_name')) {
                Schema::table('yz_goods_privilege', function (Blueprint $table) {
                    $table->string('buy_limit_name')->nullable()->comment('限时购自定义名称');
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

    }
}
