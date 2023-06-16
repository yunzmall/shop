<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_invoice')) {

            Schema::create('yz_order_invoice', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->comment('公众号');
                $table->integer('order_id')->index('order_idx')->comment('订单id');
                $table->integer('need_invoice')->comment('是否开发票 0否1是');
                $table->integer('invoice_type')->nullable()->comment('发票类型(0电子1纸质)');
                $table->integer('rise_type')->nullable()->comment('发票抬头(1个人0单位)');
                $table->string('collect_name')->nullable()->comment('抬头或单位名称');
                $table->string('email')->default('')->nullable()->comment('电子邮箱');
                $table->integer('company_number')->nullable()->comment('	单位识别码');
                $table->string('invoice')->default('')->nullable()->comment('发票图片链接');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_invoice` comment'订单--发票记录'");//表注释

        } else {
            Schema::table('yz_order_invoice', function (Blueprint $table) {

                if (!Schema::hasColumn('yz_order_invoice', 'email')) {
                    $table->string('email')->default('')->nullable()->comment('电子邮箱');
                }

                if (!Schema::hasColumn('yz_order_invoice', 'need_invoice')) {
                    $table->integer('need_invoice')->default(0)->comment('是否开发票 0否1是');
                }


                if (!Schema::hasColumn('yz_order_invoice', 'created_at')) {
                    $table->integer('updated_at')->nullable();
                    $table->integer('created_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                }


                if (Schema::hasColumn('yz_order_invoice', 'call')) {
                    $table->renameColumn('call', 'collect_name');
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
        Schema::dropIfExists('yz_order_invoice');
    }
}
