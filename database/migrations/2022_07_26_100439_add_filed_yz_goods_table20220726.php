<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledYzGoodsTable20220726 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods')) {
            Schema::table('yz_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods', 'min_price')) {
                    $table->decimal('min_price', 10,2)->nullable()->comment('最低价（开启规格即规格最低）');
                }
                if (!Schema::hasColumn('yz_goods', 'max_price')) {
                    $table->decimal('max_price', 10,2)->nullable()->comment('最高价（开启规格即规格最高）');
                }
            });

            $uniAccount = \app\common\models\UniAccount::getEnable() ?: [];
            foreach ($uniAccount as $u) {
                \app\common\facades\Setting::$uniqueAccountId = $u->uniacid;
                \YunShop::app()->uniacid = $u->uniacid;
                 \app\common\models\Goods::uniacid()->select('id')
                     ->chunk('3000',function ($goods) use ($u) {
                         $ids = $goods->pluck('id')->all();
                         if ($ids) {
                             dispatch(new \app\Jobs\SetGoodsPriceJob($ids,$u->uniacid));
                         }
                     });
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
