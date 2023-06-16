<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/6/24
 * Time: 18:10
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzBusinessDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_department')) {
            Schema::create('yz_business_department', function (Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('business_id')->comment('企业ID');
                $table->string('name')->default('')->comment('部门名称');
                $table->string('en_name')->default('')->comment('部门英文名称');
                $table->integer('level')->comment('层级');
                $table->integer('parent_id')->comment('上级部门id');
                $table->integer('wechat_department_id')->default(0)->comment('企业微信部门id');
                $table->integer('order')->comment('部门排序');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
                $table->index(['business_id','wechat_department_id'],'business_department_index_business_wechat');
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_business_department comment '企业PC端-企业部门表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_business_department');
    }
}
