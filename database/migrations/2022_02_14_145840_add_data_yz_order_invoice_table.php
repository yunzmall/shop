<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataYzOrderInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_invoice')) {
            Schema::table('yz_order_invoice', function (Blueprint $table) {
                $table->string('enterprise_id')->default('')->comment('企业唯一标识');
                $table->string('invoice_sn')->default('')->comment('发票请求流水号 (全局唯一)');
                $table->string('equipment_number')->default('')->comment('税控设备号');
                $table->integer('bill_type')->default(0)->comment('开票类型：0-蓝字发票；1-红字发票');
                $table->integer('special_type')->default(0)->comment('特殊票种：0-不是；1-农产品销售；2-农产品收购(收购票)');
                $table->integer('collection')->default(0)->comment('征收方式：0- 专普票；1-减按计增；2-差额征收');
                $table->integer('list_identification')->default(0)->comment('清单标识：0- 非清单；1- 清单');
                $table->string('xsf_name')->default('')->comment('销售方名称');
                $table->string('xsf_taxpayer')->default('')->comment('销售方纳税人识别号');
                $table->string('xsf_address')->default('')->comment('销售方地址');
                $table->string('xsf_mobile')->default('')->comment('销售方电话');
                $table->string('xsf_bank_admin')->default('')->comment('销售方银行账户');
                $table->string('gmf_taxpayer')->default('')->comment('购买方纳税人识别号');
                $table->string('gmf_address')->default('')->comment('注册地址');
                $table->string('gmf_bank')->default('')->comment('开户银行');
                $table->string('gmf_bank_admin')->default('')->comment('购买方银行账户');
                $table->string('gmf_mobile')->default('')->comment('注册电话');
                $table->string('content')->default('')->comment('发票内容');
                $table->string('drawer')->default('')->comment('开票人');
                $table->string('payee')->default('')->comment('收款人');
                $table->string('reviewer')->default('')->comment('复核人');
                $table->text('remarks')->default('')->comment('备注');
                $table->string('notice_no')->default('')->comment('通知单编号');
                $table->string('applicant')->default('')->comment('申请人');
                $table->integer('is_audit')->default(0)->comment('是否自动审核：0-非自动审核；1-自动审核');
                $table->text('detail_param')->default('')->comment('明细参数');
                $table->integer('tax_rate')->default(0)->comment('税率');
                $table->integer('zero_tax_rate')->default(0)->comment('零税率标识：0-正常税率；1-免税；2-不征税；3-普通零税率');
                $table->string('invoice_no')->default('')->comment('发票编号');
                $table->integer('invoice_nature')->default(0)->comment('发票行性质: 0-正常行;1-折扣行 (折扣票金额正);2-被折扣行\'(折扣票金额负)');
                $table->integer('status')->default(0)->comment('开票状态 0-未开票，1-开票成功，2-开票中');
                $table->integer('invoice_time')->default(0)->comment('开票时间');
                $table->string('invoice_image')->default('')->comment('发票图片地址');
                $table->integer('apply')->default(0)->comment('申请开票（0=未申请，1=已申请）');
                $table->string('col_address')->default('')->comment('收票地址');
                $table->string('col_name')->default('')->comment('收票人姓名');
                $table->string('col_mobile')->default('')->comment('收票人电话');

                if (!Schema::hasColumns('yz_order_invoice', ['uid'])) {
                    $table->integer('uid')->default(0)->comment('uid');
                }
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
