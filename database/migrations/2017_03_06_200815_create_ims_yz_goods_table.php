<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzGoodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_goods')) {
            Schema::create('yz_goods', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable()->default(0)->index('idx_uniacid')->comment('公众号');
                $table->integer('brand_id')->nullable()->comment('品牌ID');
                $table->boolean('type')->nullable()->default(1)->comment('类型');
                $table->boolean('status')->nullable()->default(1)->comment('状态');
                $table->integer('display_order')->nullable()->default(0)->comment('排序');
                $table->string('title', 100)->nullable()->default('')->comment('名称');
                $table->string('thumb')->nullable()->default('')->comment('主图');
                $table->text('thumb_url', 65535)->nullable()->comment('其他图片');
                $table->string('sku', 5)->nullable()->default('')->comment('单位');
                $table->string('description', 1000)->nullable()->default('')->comment('没有用');
                $table->text('content', 65535)->nullable()->comment('描述');
                $table->string('goods_sn', 50)->nullable()->default('')->comment('编号');
                $table->string('product_sn', 50)->nullable()->default('')->comment('条码');
                $table->decimal('market_price', 10)->nullable()->default(0.00)->comment('原价');
                $table->decimal('price', 10)->nullable()->default(0.00)->comment('现价');
                $table->decimal('cost_price', 10)->nullable()->default(0.00)->comment('成本价');
                $table->integer('stock')->nullable()->default(0)->comment('库存');
                $table->integer('reduce_stock_method')->nullable()->default(0)->comment('扣库存方式');
                $table->integer('show_sales')->nullable()->default(0)->comment('销量');
                $table->integer('real_sales')->nullable()->default(0)->comment('销量');
                $table->decimal('weight', 10)->nullable()->default(0.00)->comment('重量');
                $table->integer('has_option')->nullable()->default(0)->comment('是否启用规格');
                $table->boolean('is_new')->nullable()->default(0)->index('idx_isnew');
                $table->boolean('is_hot')->nullable()->default(0)->index('idx_ishot');
                $table->boolean('is_discount')->nullable()->default(0)->index('idx_isdiscount');
                $table->boolean('is_recommand')->nullable()->default(0)->index('idx_isrecommand');
                $table->boolean('is_comment')->nullable()->default(0)->index('idx_iscomment');
                $table->boolean('is_deleted')->default(0)->index('idx_deleted');
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('is_plugin')->unsigned()->default(0);
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
		Schema::dropIfExists('yz_goods');
	}

}
