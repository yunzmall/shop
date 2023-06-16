<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzPostageIncludedCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_postage_included_category')) {
            Schema::create('yz_postage_included_category', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('sort')->comment('排序');
                $table->string('name')->comment('分类名');
                $table->tinyInteger('is_display')->comment('是否展示');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_postage_included_category` comment '包邮分类表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_postage_included_category')) {
            Schema::dropIfExists('yz_postage_included_category');
        }
    }
}
