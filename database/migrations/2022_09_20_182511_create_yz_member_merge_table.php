<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzMemberMergeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_member_merge')) {
            Schema::create('yz_member_merge', function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('uniacid')->comment('公众号')->index('uniacid_idx');
                    $table->integer('before_uid')->nullable()->comment('合并前uid');
                    $table->string('after_uid')->nullable()->comment('合并后uid');
                    $table->string('before_amount')->nullable()->comment('合并前余额');
                    $table->string('after_amount')->nullable()->comment('合并后余额');
                    $table->string('before_point')->nullable()->comment('合并前积分');
                    $table->string('after_point')->nullable()->comment('合并后积分');
                    $table->string('before_love_usable')->nullable()->comment('合并前可用爱心值');
                    $table->string('after_love_usable')->nullable()->comment('合并后可用爱心值');
                    $table->string('before_love_froze')->nullable()->comment('合并前冻结爱心值');
                    $table->string('after_love_froze')->nullable()->comment('合并后冻结爱心值');
                    $table->string('before_mobile')->nullable()->comment('合并前手机号');
                    $table->string('after_mobile')->nullable()->comment('合并后手机号');
                    $table->text('set_content')->nullable()->comment('设置内容');
                    $table->tinyInteger('merge_type')->nullable()->comment('合并类型1绑定手机合并2点击合并3自动合并');
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_merge` comment'会员--合并记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_member_merge');
    }
}
