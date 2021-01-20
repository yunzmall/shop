<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberCertifiedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('yz_member_certified')) {
            Schema::create('yz_member_certified',function (Blueprint $table){
                $table->increments('id')->comment('主键id');
                $table->string('realname',10)->comment('真实姓名');
                $table->string('idcard',30)->nullable()->comment('身份证');
                $table->string('remark',50)->nullable()->comment('备注');
                $table->integer('uniacid')->default(0);
                $table->integer('member_id')->comment('关联会员id');
                $table->integer('order_id')->default(0)->nullable()->comment('关联订单id');
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
        Schema::dropIfExists('yz_member_certified');
    }
}
