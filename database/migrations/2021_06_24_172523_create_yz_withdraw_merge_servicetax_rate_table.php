<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/24
 * Time: 18:10
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzWithdrawMergeServicetaxRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_withdraw_merge_servicetax_rate')) {
            Schema::create('yz_withdraw_merge_servicetax_rate', function (Blueprint $table) {
                $table->increments('id', true)->comment('主键ID');
                $table->integer('uniacid')->comment('公众号ID');
                $table->integer('withdraw_id')->comment('提现记录id');
                $table->decimal('servicetax_rate',10,2)->comment('合并劳务费比例');
                $table->tinyInteger('is_disabled')->default(0)->comment('是否有效 0有效 1失效');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_withdraw_merge_servicetax_rate comment '合并提现劳务费比例表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_withdraw_merge_servicetax_rate');
    }
}
