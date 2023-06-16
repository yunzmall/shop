<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzGoodsCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_goods_category')) {
            Schema::create('yz_goods_category', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('goods_id')->nullable();
                $table->integer('category_id')->nullable()->comment('分类ID');
                $table->string('category_ids')->nullable()->comment('层级分类ID');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });


            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_goods_category` comment'商品--分类关联'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_goods_category');
	}

}
