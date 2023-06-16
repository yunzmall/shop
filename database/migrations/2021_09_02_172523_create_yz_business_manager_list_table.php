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

class CreateYzBusinessManagerListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_manager_list')) {
            Schema::create('yz_business_manager_list', function (Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('uid')->comment('会员id');
                $table->integer('business_id')->comment('企业id');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_business_manager_list comment '企业PC端-管理员表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_business_manager_list');
    }
}
