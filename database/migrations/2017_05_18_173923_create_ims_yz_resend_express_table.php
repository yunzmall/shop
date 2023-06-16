<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzResendExpressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_resend_express')) {
            Schema::create('yz_resend_express', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('refund_id')->default(0)->index('idx_order_id')->comment('售后表id');
                $table->string('express_company_name', 50)->default('0')->comment('物流公司名称');
                $table->string('express_sn', 50)->default('0')->comment('物流单号');
                $table->string('express_code', 20)->default('0')->comment('物流公司编码');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_resend_express comment '商城--售后卖家发货物流'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_resend_express');
	}

}
