<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentPointToYzGoodsSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (\Schema::hasTable('yz_goods_sale')) {
            Schema::table('yz_goods_sale', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_sale', 'first_parent_point')) {
                    $table->string('first_parent_point')->nullable()->comment('一级上级赠送积分');
                }
                if (!Schema::hasColumn('yz_goods_sale', 'second_parent_point')) {
                    $table->string('second_parent_point')->nullable()->comment('二级上级赠送积分');
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

    }
}
