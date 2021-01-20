<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzCouponSlideShowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_coupon_slide_show')) {
            Schema::create('yz_coupon_slide_show', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号');
                $table->integer('sort')->default(0)->comment('排序');
                $table->string('title')->comment('标题');
                $table->string('slide_pic')->comment('幻灯片图片');
                $table->string('slide_link')->nullable()->comment('幻灯片连接');
                $table->string('mini_link')->nullable()->comment('小程序连接');
                $table->tinyInteger('is_show')->default(0)->comment('是否显示');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
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
        //
    }
}
