<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzCouponTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_coupon')) {
            Schema::create('yz_coupon', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable()->default(0)->index('idx_uniacid');
                $table->integer('cat_id')->nullable()->default(0)->comment('分类ID');
                $table->string('name')->nullable()->default('')->comment('优惠券名称');
                $table->boolean('get_type')->nullable()->default(0)->comment('获取优惠券的方式');
                $table->integer('level_limit')->nullable()->comment('领取条件 - 会员等级');
                $table->integer('get_max')->nullable()->default(0)->comment('每个人的限领数量');
                $table->boolean('use_type')->nullable()->default(0)->comment('使用方式');
                $table->string('bgcolor')->nullable()->default('')->comment('背景颜色');
                $table->integer('enough')->unsigned()->nullable()->default(0)->comment('使用条件(消费金额前提)');
                $table->boolean('coupon_type')->nullable()->default(0)->comment('优惠券类型');
                $table->boolean('time_limit')->nullable()->default(0)->comment('使用时间限制');
                $table->integer('time_days')->nullable()->default(0)->comment('使用时间限制');
                $table->integer('time_start')->nullable()->default(0)->comment('起始使用时间');
                $table->integer('time_end')->nullable()->default(0)->comment('截止使用时间');
                $table->boolean('coupon_method')->nullable()->comment('优惠方式');
                $table->decimal('deduct', 10)->nullable()->default(0.00)->comment('优惠立减');
                $table->decimal('discount', 10)->nullable()->default(0.00)->comment('优惠打折');
                $table->string('thumb')->nullable()->default('')->comment('缩略图');
                $table->text('desc', 65535)->nullable()->comment('描述说明');
                $table->integer('total')->nullable()->default(0)->comment('优惠券发放总数');
                $table->boolean('status')->nullable()->default(0)->comment('是否启用');
                $table->text('resp_desc', 65535)->nullable()->comment('推送说明');
                $table->string('resp_thumb')->nullable()->default('')->comment('推送缩略图');
                $table->string('resp_title')->nullable()->default('')->comment('推送标题');
                $table->string('resp_url')->nullable()->default('')->comment('推送链接');
                $table->string('remark', 1000)->nullable()->default('')->comment('备注');
                $table->integer('display_order')->nullable()->default(0)->comment('排序');
                $table->integer('supplier_uid')->nullable()->default(0)->comment('供应商uid');
                $table->text('cashiersids', 65535)->nullable()->comment('收银台id');
                $table->text('cashiersnames', 65535)->nullable()->comment('收银台名称');
                $table->text('category_ids', 65535)->nullable()->comment('分类id');
                $table->text('categorynames', 65535)->nullable()->comment('分类名称');
                $table->text('goods_names', 65535)->nullable()->comment('商品名称');
                $table->text('goods_ids', 65535)->nullable()->comment('商品id');
                $table->text('supplierids', 65535)->nullable()->comment('供应商ids');
                $table->text('suppliernames', 65535)->nullable()->comment('供应商名称');
                $table->integer('createtime')->nullable()->default(0);
                $table->integer('created_at')->unsigned()->nullable();
                $table->integer('updated_at')->unsigned()->nullable();
                $table->integer('deleted_at')->unsigned()->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_coupon comment '优惠券--优惠券表'");//表注释
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_coupon');
    }

}
