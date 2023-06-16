<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateToAliasYzGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods')) {
            Schema::table('yz_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods', 'alias')) {
                    $table->string('alias')->default('')->comment('商品别名');
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
        Schema::table('yz_goods', function (Blueprint $table) {
            $table->dropColumn('alias');
        });
    }
}
