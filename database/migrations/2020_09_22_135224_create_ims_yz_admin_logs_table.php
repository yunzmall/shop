<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzAdminLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('yz_admin_logs')){
            Schema::create('yz_admin_logs',function (Blueprint $table){
                //管理员登录日志
                $table->increments('id');
                $table->integer('admin_uid')->comment('类型;1-管理员用户ID')->default(0);
                $table->text('remark')->comment('备注');
                $table->string('ip', 15)->comment('ip');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable()->default(0);
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
        Schema::dropIfExists('yz_admin_logs');
    }
}
