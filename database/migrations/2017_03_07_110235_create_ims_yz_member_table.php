<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member')) {
            Schema::create('yz_member', function (Blueprint $table) {
                $table->integer('member_id')->index('idx_member_id')->comment('会员id');
                $table->integer('uniacid')->index('idx_uniacid')->comment('公众号');
                $table->integer('parent_id')->nullable()->comment('上级id');
                $table->integer('group_id')->default(0)->comment('会员组id');
                $table->integer('level_id')->default(0)->comment('会员等级id');
                $table->integer('inviter')->nullable()->default(0)->comment('是否有上线 0-没有锁定上线;1-有锁定上线');
                $table->boolean('is_black')->default(0)->comment('黑名单 0-普通会员;1-黑名单会员');
                $table->string('province_name', 15)->nullable()->comment('省名称');
                $table->string('city_name', 15)->nullable()->comment('市名称');
                $table->string('area_name', 15)->nullable()->comment('区名称');
                $table->integer('province')->nullable()->comment('省编号');
                $table->integer('city')->nullable()->comment('市编号');
                $table->integer('area')->nullable()->comment('区编号');
                $table->text('address', 65535)->nullable()->comment('详细地址');
                $table->string('referralsn', 255)->nullable();
                $table->boolean('is_agent')->nullable()->comment('是否有审核资格 0-没有;1-有');
                $table->string('alipayname')->nullable()->comment('支付宝账号名称');
                $table->string('alipay')->nullable()->comment('支付宝账号');
                $table->text('content', 65535)->nullable()->comment('备注');
                $table->integer('status')->nullable()->default(0)->comment('推广审核状态0-未申请；1-审核中；2-审核通过');
                $table->integer('child_time')->nullable()->default(0)->comment('成为下线时间');
                $table->integer('agent_time')->nullable()->default(0)->comment('获取发展下线资格时间');
                $table->integer('apply_time')->nullable()->default(0)->comment('资格申请时间');
                $table->string('relation', 255)->nullable()->comment('会员关系字段');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member` comment '会员--商城辅助表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member');
	}

}
