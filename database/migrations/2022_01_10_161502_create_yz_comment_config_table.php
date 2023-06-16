<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzCommentConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_comment_config')) {
            Schema::create('yz_comment_config', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid')->default(0)->index('idx_uniacid');
                $table->tinyInteger('is_comment_audit')->default(0)->comment('评论审核：1-开启');
                $table->tinyInteger('is_default_good_reputation')->default(0)->comment('默认好评：1-开启');
                $table->tinyInteger('is_order_comment_entrance')->default(0)->comment('订单显示评论入口：1-开启');
                $table->tinyInteger('is_additional_comment')->default(0)->comment('追评：1-开启');
                $table->tinyInteger('is_score_latitude')->default(0)->comment('评分维度：1-开启');
                $table->char('top_sort')->default('asc')->comment('置顶排序：asc升序，desc倒序')->index('idx_top_sort');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_comment_config` comment '评论设置表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_comment_config');
    }
}
