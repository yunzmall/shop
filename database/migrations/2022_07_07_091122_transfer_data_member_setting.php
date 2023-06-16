<?php

use app\common\facades\Setting;
use app\common\helpers\Cache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TransferDataMemberSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_setting')) {
            $uniAccount = \app\common\models\UniAccount::getEnable();
            foreach ($uniAccount as $u) {
                \YunShop::app()->uniacid = $u->uniacid;
                Setting::$uniqueAccountId = $u->uniacid;
                //登录方式设置初始化
                $registerSet = Setting::get('shop.register');
                $memberSet = Setting::get('shop.member');
                if (isset($registerSet['login_mode'])) {//设置过这个
                    continue;
                }
                $registerSet['login_mode'] = $memberSet['mobile_login_code'] ? ['mobile_code'] : ['password'];
                Setting::set('shop.register',$registerSet);
                if (Cache::has('shop_register')) {
                    Cache::forget('shop_register');
                }

                //强制绑定手机页面配置
                $bind_mobile_page = [];
                if ($memberSet['is_bind_mobile'] ==  "2") {
                    $bind_mobile_page = ['member_center'];
                } elseif ($memberSet['is_bind_mobile'] ==  3) {
                    $memberSet['is_bind_mobile'] = "2";
                    $bind_mobile_page = ['goods_detail'];
                } elseif ($memberSet['is_bind_mobile'] ==  4) {
                    $memberSet['is_bind_mobile'] = "2";
                    $bind_mobile_page = ['promotion_center'];
                }
                $memberSet['bind_mobile_page'] = $bind_mobile_page;
                $memberSet['is_custom_register'] = "1";
                $memberSet['form_id_register'] = "1";
                if (Cache::has('shop_member')) {
                    Cache::forget('shop_member');
                }
                Setting::set('shop.member', $memberSet);

                //会员资料设置初始化
                $formSet = json_decode(Setting::get('shop.form'),true);
                $formSet['base']['basic_register'] = "1";
                $formSet['base']['form_register'] = "1";
                Setting::set('shop.form', json_encode($formSet));
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
