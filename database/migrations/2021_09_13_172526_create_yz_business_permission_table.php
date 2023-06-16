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

class CreateYzBusinessPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_permission')) {
            Schema::create('yz_business_permission', function (Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('business_id')->comment('企业ID');
                $table->integer('department_id')->default(0)->comment('部门id');
                $table->integer('staff_id')->default(0)->comment('员工id');
                $table->tinyInteger('type')->comment('1部门员工权限 2部门领导权限 3用户独立权限');
                $table->string('module')->default('')->comment('模块名或插件名,如admin,frontend,yun-sign');
                $table->string('route')->default('')->comment('路由名');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
                $table->index(['business_id', 'department_id', 'staff_id'],'business_permission_index_business_department_staff');
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_business_permission comment '企业PC端-部门员工权限表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_business_permission');
    }
}
