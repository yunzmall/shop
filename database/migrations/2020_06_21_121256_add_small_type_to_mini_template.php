<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmallTypeToMiniTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_mini_template_corresponding')) {
            Schema::create("yz_mini_template_corresponding", function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid');
                $table->integer('small_type')->nullable()->comment("类型id");
                $table->string("template_id")->nullable()->comment("微信模板id");
                $table->string('template_name')->nullable()->comment("微信模板名称");
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            $time = time();

            $uniAccount = \app\common\models\UniAccount::getEnable() ?: [];
            $all = [];

            foreach ($uniAccount as $u) {
                $uniacid = $u->uniacid;
                $data = [
                    ["uniacid"=>$uniacid,"small_type"=>1,"template_name"=>"订单支付成功通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>1,"template_name"=>"订单发货通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>1,"template_name"=>"确认收货通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>2,"template_name"=>"退款成功通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>3,"template_name"=>"提现状态通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>3,"template_name"=>"提现到账通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>4,"template_name"=>"新订单提醒","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>4,"template_name"=>"提现状态通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>4,"template_name"=>"提现到账通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>5,"template_name"=>"新订单提醒","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>6,"template_name"=>"直播审核结果通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>6,"template_name"=>"粉丝下单通知","created_at"=>$time,"updated_at"=>$time],
                    ["uniacid"=>$uniacid,"small_type"=>6,"template_name"=>"佣金到账提醒","created_at"=>$time,"updated_at"=>$time]
                ];
                $all = array_merge_recursive($all,$data);
            }

            \Illuminate\Support\Facades\DB::table('yz_mini_template_corresponding')->insert($all);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_mini_template_corresponding');
    }
}
