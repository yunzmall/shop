<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/5/19
 * Time: 下午1:48
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberPositionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_member_position')) {
            Schema::create('yz_member_position', function (Blueprint $table) {
                $table->increments('id', true)->comment('主键ID');
                $table->integer('uniacid')->default(0)->comment('公众号ID');
                $table->integer('member_id')->default(0)->comment('会员ID');

                $table->string('province_name')->default('')->comment('省');
                $table->string('city_name')->default('')->comment('市');
                $table->string('district_name')->default('')->comment('区');

                $table->string('longitude')->default('')->comment('经度');
                $table->string('latitude')->default('')->comment('纬度');

                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_member_position comment '会员定位表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_member_position');
    }
}
