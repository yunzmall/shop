<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnToYzCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_comment')) {
            Schema::table('yz_comment', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_comment', 'is_show')) {
                    $table->tinyInteger('is_show')->default(1)->comment('是否显示：1-显示')->index('idx_is_show');
                }
                if (!Schema::hasColumn('yz_comment', 'is_top')) {
                    $table->tinyInteger('is_top')->default(0)->comment('是否置顶：1-置顶')->index('idx_is_top');
                }
                if (!Schema::hasColumn('yz_comment', 'score_latitude')) {
                    $table->text('score_latitude',65535)->nullable()->comment('评分纬度');
                }
                if (!Schema::hasColumn('yz_comment', 'audit_status')) {
                    $table->tinyInteger('audit_status')->default(0)->comment('审核状态：0-不需要审核，1-通过，-1 = 驳回，2-待审核')->index('idx_audit_status');
                }
                if (!Schema::hasColumn('yz_comment', 'additional_comment_id')) {
                    $table->integer('additional_comment_id')->default(0)->comment('追评ID');
                }
                if (!Schema::hasColumn('yz_comment', 'has_default_good_reputation')) {
                    $table->tinyInteger('has_default_good_reputation')->default(0)->comment('是否默认好评，1-是');
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
        Schema::table('yz_goods_sale', function (Blueprint $table) {
            $table->dropColumn('is_show');
            $table->dropColumn('is_top');
            $table->dropColumn('score_latitude');
            $table->dropColumn('audit_status');
            $table->dropColumn('additional_comment_id');
            $table->dropColumn('has_default_good_reputation');
        });
    }
}
