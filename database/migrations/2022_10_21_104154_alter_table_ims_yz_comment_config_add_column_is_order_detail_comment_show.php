<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableImsYzCommentConfigAddColumnIsOrderDetailCommentShow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_comment_config')) {
            Schema::table('yz_comment_config', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_comment_config', 'is_order_detail_comment_show')) {
                    $table->tinyInteger('is_order_detail_comment_show')->default(1)->comment('商品详情评论显示 1 显示 0不显示');
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
        Schema::table('yz_comment_config', function (Blueprint $table) {
            if (Schema::hasColumn('yz_comment_config', 'is_order_detail_comment_show')) {
                $table->dropColumn(['is_order_detail_comment_show']);
            }
        });
    }
}
