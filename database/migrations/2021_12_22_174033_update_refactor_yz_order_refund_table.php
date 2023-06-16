<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRefactorYzOrderRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_refund')) {
            Schema::table('yz_order_refund', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_refund', 'receive_status')) {
                    $table->tinyInteger('receive_status')->default(0)->comment('收货状态 0未收货 1已收货');
                }
                if (!Schema::hasColumn('yz_order_refund', 'part_refund')) {
                    $table->tinyInteger('part_refund')->default(0)->comment('订单部分退款 0否 1是');
                }

            });
        }

        if (Schema::hasTable('yz_order_goods')) {
            Schema::table('yz_order_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_goods', 'is_refund')) {
                    $table->integer('is_refund')->default(0)->comment( '商品退款次数');
                }

            });
        }

        if (Schema::hasTable('yz_resend_express')) {
            Schema::table('yz_resend_express', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_resend_express', 'pack_goods')) {
                    $table->text('pack_goods')->nullable()->comment( '发货商品数据冗余');
                }
            });
        }

        if (Schema::hasTable('yz_return_express')) {
            Schema::table('yz_return_express', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_return_express', 'images')) {
                    $table->text('images')->nullable()->comment( '图片，text类型为了以后多图');
                }

                if (!Schema::hasColumn('yz_return_express', 'way_id')) {
                    $table->string('way_id')->nullable()->comment( '退货方式唯一编号');
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
    }
}
