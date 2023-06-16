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

class CreateYzBusinessDepartmentStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_department_staff')) {
            Schema::create('yz_business_department_staff', function (Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('business_id')->comment('企业ID');
                $table->integer('staff_id')->default(0)->comment('企业员工id,yz_business_staff表id');
                $table->integer('department_id')->comment('部门id');
                $table->integer('is_leader')->comment('是否部门领导');
                $table->integer('sort')->comment('部门排序');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
                $table->index(['business_id', 'department_id', 'staff_id'], 'business_department_staff_index_bds');
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_business_department_staff comment '企业PC端-企业员工表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_business_department_staff');
    }
}
