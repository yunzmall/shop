<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterYzMemberIncomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_income')) {
            Schema::table('yz_member_income', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member_income', 'dividend_code')) {
                    $table->integer('dividend_code')->after('member_id')->nullable()->comment('分红插件');
                }
                if (!Schema::hasColumn('yz_member_income', 'order_sn')) {
                    $table->string('order_sn')->nullable()->default('')->comment('订单号');
                }
                if (Schema::hasColumn('yz_member_income', 'incometable_type')) {
                    $table->string('incometable_type')->nullable()->default('')->change();
                }
            });
            foreach (\app\common\services\income\IncomeService::getClass() as $code =>$class) {
                \Illuminate\Support\Facades\DB::table('yz_member_income')->where('incometable_type',$class)->update(['dividend_code'=>$code]);
            }
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
