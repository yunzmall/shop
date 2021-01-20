<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/9
 * Time: 下午5:26
 */

namespace app\backend\modules\setting\controllers;

use app\backend\modules\goods\models\Goods;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\helpers\Cache;
use app\common\helpers\Url;
use app\common\facades\Setting;
use app\common\models\AccountWechats;
use app\common\models\MemberLevel;
use app\common\models\notice\MessageTemp;
use app\common\models\Protocol;
use app\common\modules\refund\services\RefundService;
use app\common\services\MyLink;
use app\common\services\Utils;
use Hamcrest\Core\Set;
use Mews\Captcha\Captcha;
use Yunshop\Diyform\models\DiyformTypeModel;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use app\common\models\PayType;
use app\common\models\notice\MinAppTemplateMessage;
use app\backend\modules\member\models\MemberShopInfo;

class ShopController extends UploadVerificationBaseController
{
    /**
     * 商城设置
     * @return mixed
     */
    public function index()
    {
        if(request()->ajax()){
            $shop = Setting::get('shop.shop');
            //dd(yz_tomedia($shop['logo_url']));
            $shop['logo_url'] = !empty($shop['logo_url']) ? $shop['logo_url'] : yz_tomedia($shop['logo']);
            $shop['signimg_url'] = !empty($shop['signimg_url']) ? $shop['signimg_url'] : yz_tomedia($shop['signimg']);
            $shop['copyrightImg_url'] = !empty($shop['copyrightImg_url']) ? $shop['copyrightImg_url'] : yz_tomedia($shop['copyrightImg']);
            $level = MemberLevel::get(['id','level_name']);
            $requestModel = request()->shop;
            if ($requestModel) {
                if(Cache::has('shop_setting')){
                    Cache::forget('shop_setting');
                }
                $requestModel['credit']=empty($requestModel['credit'])?"余额":$requestModel['credit'];
                if (Setting::set('shop.shop', $requestModel)) {
                    return $this->successJson('商城设置成功');
                } else {
                    return $this->errorJson('商城设置失败');
                }
            }

            return $this->successJson('请求接口成功',[
                'shop' => $shop,
                'level' => $level
            ]);
        }
        return view('setting.shop.shop');
    }

    /**
     * 会员设置
     * @return mixed
     */
    public function member()
    {
        if(request()->ajax()){
            $member = Setting::get('shop.member');
            $shop = Setting::get('shop.shop');
            $requestModel = request()->member;

            $member['headimg_url'] = !empty($member['headimg_url']) ? $member['headimg_url'] : yz_tomedia($member['headimg']);
            if ($requestModel) {
                if($requestModel['is_bind_mobile'] !== '0' && $requestModel['invite_page'] == '1'){
                    return $this->errorJson('强制绑定手机号跟邀请页面不能同时开启');
                }
                if(Cache::has('shop_member')){
                    Cache::forget('shop_member');
                }

                if (Setting::set('shop.member', $requestModel)) {
                    return $this->successJson('会员设置成功');
                } else {
                    $this->errorJson('会员设置失败');
                }
            }
            $is_diyform = \YunShop::plugin()->get('diyform');

            $diyForm = [];
            if($is_diyform){
                $diyForm = DiyformTypeModel::getDiyformList()->get();

            }
            return $this->successJson('成功',  [
                'set' => $member,
                'shop' => $shop,
                'is_diyform' => $is_diyform,
                'diyForm' => $diyForm,
            ]);
        }
        return view('setting.shop.member')->render();
    }

    /**
     * 会员设置
     * @return mixed
     */
    public function register()
    {
        $register = Setting::get('shop.register');
        $shop = Setting::get('shop.shop');
        $requestModel = request()->register;
        if ($requestModel) {
            if(Cache::has('shop_register')){
                Cache::forget('shop_register');
            }

            if (!empty($requestModel['protocol'])) {
                $shop = Protocol::uniacid()->first();
                if(empty($shop)){
                    $shop = new Protocol();
                    $shop->uniacid = \YunShop::app()->uniacid;
                }

                $shop->status = $requestModel['protocol']['status'];
                $shop->title = $requestModel['protocol']['title'];
                $shop->content = $requestModel['protocol']['content'];
                $shop->save();
                $requestModel['protocol']['content'] = '';
            }

            unset($requestModel['protocol']['content']);


            if (Setting::set('shop.register', $requestModel)) {
                return $this->successJson('设置成功', Url::absoluteWeb('setting.shop.register'));
            } else {
                $this->errorJson('设置失败');
            }
        }

        $protocol = Protocol::uniacid()->first();

        return view('setting.shop.register',[
            'register' => $register,
            'protocol' => $protocol,
        ])->render();
    }

    /**
     * 订单设置
     * @return mixed|string
     * @throws \Throwable
     */

    public function order()
    {
        if(request()->ajax()){
            $order = Setting::get('shop.order');

            $requestModel = request()->order;
            if ($requestModel) {
                if(Cache::has('shop_order')){
                    Cache::forget('shop_order');
                }
                if (Setting::set('shop.order', $requestModel)) {
                    return $this->successJson('订单设置成功');
                } else {
                    $this->errorJson('订单设置失败');
                }
            }
            if (!empty($order['goods'])) {
                $goods = Goods::select('id', 'title', 'thumb')
                    ->where('status', 1)
                    ->whereIn('id', $order['goods'])
                    ->where('plugin_id', 0)
                    ->get();
                if (!$goods->isEmpty()) {
                    $goods->map(function($q){
                        return $q->thumb_url = yz_tomedia($q->thumb);
                    });
                }
            } else {
                $goods = [];
            }
            $order['goods'] = array_values($order['goods']);
            return $this->successJson('请求接口成功 ',  [
                'set' => $order,
                'goods' => $goods,
            ]);
        }

        return view('setting.shop.order');
    }

    /**
     * 模板设置
     * @return mixed
     */

    public function temp()
    {
        if(request()->ajax()){
            $temp = Setting::get('shop.temp');
            $styles = [];//模板数据,数据如何来的待定?
            $styles_pc = [];//pc模板数据,待定
            $requestModel = \YunShop::request()->temp;
            if ($requestModel) {
                if (Setting::set('shop.temp', $requestModel)) {
                    return $this->successJson('模板设置成功');
                } else {
                    $this->errorJson('模板设置失败');
                }
            }
            return $this->successJson('请求接口成功', [
                'set' => $temp,
                'styles' => $styles,
                'styles_pc' => $styles_pc
            ]);
        }
        return view('setting.shop.temp');
    }

    /**
     * 分类层级设置
     * @return mixed
     */

    public function category()
    {
        if(request()->ajax()){
            $category = Setting::get('shop.category');
            $category['cat_adv_img_url'] = !empty( $category['cat_adv_img_url']) ? $category['cat_adv_img_url'] : yz_tomedia( $category['cat_adv_img']);
            $requestModel = request()->category;
            if ($requestModel) {
                if (Setting::set('shop.category', $requestModel)) {
                    if(Cache::has('shop_category')){
                        Cache::forget('shop_category');
                    }
                    return $this->successJson(' 分类层级设置成功');
                } else {
                    $this->errorJson('分类层级设置失败');
                }
            }
            return $this->successJson('请求接口成功',  [
                'set' => $category,
            ]);
        }
        return view('setting.shop.category');
    }


    /**
     * 联系方式设置
     * @return mixed
     */

    public function contact()
    {
        if(request()->ajax()){
            $contact = Setting::get('shop.contact');
            $requestModel = request()->contact;
            if ($requestModel) {
                if (Setting::set('shop.contact', $requestModel)) {
                    return $this->successJson(' 联系方式设置成功');
                } else {
                    $this->errorJson('联系方式设置失败');
                }
            }
            return $this->successJson('请求接口成功',  [
                'set' => $contact,
            ]);
        }
        return view('setting.shop.contact');
    }


    /**
     * 短信设置
     * @return mixed
     */

    public function sms()
    {
        if(request()->ajax()){
            $sms = Setting::get('shop.sms');
            $requestModel = request()->sms;
            if ($requestModel) {
                if (Setting::set('shop.sms', $requestModel)) {
                    return $this->successJson(' 短信设置成功', Url::absoluteWeb('setting.shop.sms'));
                } else {
                    $this->errorJson('短信设置失败',  Url::absoluteWeb('setting.shop.sms'));
                }
            }
            return $this->successJson('请求接口成功',  [
                'set' => $sms,
            ]);
        }
        return view('setting.shop.sms');
    }

    //验证码测试
    public static function captchapp()
    {
        $phrase = new PhraseBuilder();
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);

        $builder->setBackgroundColor(150, 150, 150);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);

        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        \Session::flash('code', $phrase);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: image/jpeg');
        $builder->output();
    }

    /**
     * 分享引导设置
     * @return mixed
     */

    public function share()
    {
        if(request()->ajax()){
            $share = Setting::get('shop.share');
            $share['follow_img_url'] =  !empty($share['follow_img_url']) ? $share['follow_img_url'] : yz_tomedia($share['follow_img']);
            $share['icon_url'] = !empty($share['icon_url']) ? $share['icon_url'] : yz_tomedia($share['icon']);
            $requestModel = request()->share;
            if ($requestModel) {
                if (Setting::set('shop.share', $requestModel)) {
                    return $this->successJson(' 引导分享设置成功');
                } else {
                    $this->errorJson('引导分享设置失败');
                }
            }
            return $this->successJson('请求接口成功', [
                'set' => $share,
            ]);
        }
        return view('setting.shop.share');
    }

    /**
     * 消息提醒设置
     * @return mixed
     */

    public function notice()
    {
        if(request()->ajax()){
            $default_temp_id = MessageTemp::getDefaultList();
            $notice = Setting::get('shop.notice');
            $default_temp = $this->getIsDefaultTemp($default_temp_id, $notice);
            $requestModel = request()->yz_notice;
            $temp_list = MessageTemp::getList()->toArray();
            if (!empty($requestModel)) {
                 foreach ($requestModel['salers'] as $key=>&$item){
                     $item['openid'] = $item['has_one_fans']['openid'];
                     unset($item['has_one_fans']);
                 }
                if (Setting::set('shop.notice', $requestModel)) {
                    return $this->successJson(' 消息提醒设置成功');
                } else {
                    $this->errorJson('消息提醒设置失败');
                }
            }
            foreach ($notice['salers'] as $key=>&$item){
                if(strstr($item['avatar'],'http') && !strstr($item['avatar'],'https')) {
                    $item['avatar'] = str_replace('http', 'https', $item['avatar']);
                }
            }
            $notice['salers'] = array_values($notice['salers']);
            return $this->successJson('请求接口成功' , [
                'set' => $notice,
                'temp_list' => $temp_list,
                'default_temp' => $default_temp,
            ]);
        }

        return view('setting.shop.notice');
    }

    private function getIsDefaultTemp($default_temp_id, $notice)
    {
        $default_temp = [];
        foreach ($notice as $k=>$value){
            if(in_array($value, $default_temp_id)){
                $default_temp[$k] = 1;
            }else{
                $default_temp[$k] = 0;
            }
        }
        unset($default_temp['toggle'], $default_temp['salers']);
        return $default_temp;
    }
    /**
     * 小程序消息提醒设置
     * @return mixed
     */
    public function miniNotice()
    {
        $notice = Setting::get('shop.miniNotice');
//        $salers = []; //订单通知的商家列表,数据如何取待定?
        //$new_type = []; //通知方式的数组,数据如何来的待定?
        $requestModel = \YunShop::request()->yz_notice;

        $temp_list = MessageTemp::getList();

        if (!empty($requestModel)) {
            if (Setting::set('shop.miniNotice', $requestModel)) {
                return $this->message(' 消息提醒设置成功', Url::absoluteWeb('setting.shop.notice'));
            } else {
                $this->error('消息提醒设置失败');
            }
        }

        return view('setting.shop.mini-notice', [
            'set' => $notice,
            'temp_list' => $temp_list
        ])->render();
    }

    /**
     * 交易设置
     * @return mixed
     */
    public function trade()
    {
        if(request()->ajax()){
            $trade = Setting::get('shop.trade');
            $requestModel = request()->trade;
            if ($requestModel) {
                if (Setting::set('shop.trade', $requestModel)) {
                    return $this->successJson(' 交易设置成功');
                } else {
                    return $this->errorJson('交易设置失败');
                }
            }
            return $this->successJson('请求接口成功', [
                'set' => $trade
            ]);
        }
        return view('setting.shop.trade')->render();
    }

    /**
     * 支付方式设置
     * @return mixed
     */
    //支付设置上传文件接口
    public function newUpload()
    {
        $updatefile = $this->updateFile($_FILES);
        if (!is_null($updatefile)) {
            if ($updatefile['status'] == -1) {
                return $this->errorJson('上传文件类型错误', Url::absoluteWeb('setting.shop.pay'));
            }

            if ($updatefile['status'] == 0) {
                return $this->errorJson('上传文件失败', Url::absoluteWeb('setting.shop.pay'));
            }

           return $this->successJson('文件上传成功',['data' => $updatefile]);
        }else{
            return $this->errorJson('上传文件类型错误', Url::absoluteWeb('setting.shop.pay'));
        }
    }
    public function pay()
    {
        $pay = Setting::get('shop.pay');

        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);

        if (empty($pay['weixin_appid']) && empty($pay['weixin_secret']) && !empty($account)) {
            $pay['weixin_appid'] = $account->key;
            $pay['weixin_secret'] = $account->secret;
        }

        $data = [
            'weixin_jie_cert' => '',
            'weixin_jie_key' => '',
            'weixin_jie_root' => ''
        ];//借用微信支付证书,在哪里取得数据待定?
        $requestModel = request()->pay;

        if ($requestModel) {
            if (isset($requestModel['weixin_version']) && $requestModel['weixin_version'] == 1) {
                if (!empty($requestModel['new_weixin_cert']) && !empty($requestModel['new_weixin_key'])) {
                    $updatefile_v2 = $this->updateFileV2(['weixin_cert' => $requestModel['new_weixin_cert'], 'weixin_key' => $requestModel['new_weixin_key']]);


                    if ($updatefile_v2['status'] == 0) {
                        return $this->errorJson('文件保存失败1', Url::absoluteWeb('setting.shop.pay'));
                    }

                    $requestModel = array_merge($requestModel, $updatefile_v2['data']);
                }
            }


            if (isset($pay['secret']) && 1 == $pay['secret']) {
                Utils::dataEncrypt($requestModel);
            }
            if (Setting::set('shop.pay', $requestModel)) {
                (new \app\common\services\operation\ShopPayLog(['old' => $pay, 'new' => $requestModel], 'update'));
                $this->setAlipayParams($requestModel);
                return $this->successJson('支付方式设置成功', Url::absoluteWeb('setting.shop.pay'));
            } else {
                $this->errorJson('支付方式设置失败');
            }
        }

        if (isset($pay['secret']) && 1 == $pay['secret']) {
            try {
                Utils::dataDecrypt($pay);
            } catch (\Exception $e) {
                \Log::debug('------error msg------', [$e->getMessage()]);
            }
        }
        return view('setting.shop.pay', [
            'set' => json_encode($pay),
            'data' =>  json_encode($data),
        ])->render();

    }

    private function setAlipayParams($data)
    {
        Setting::set('alipay.pem', storage_path() . '/cert/cacert.pem');
        Setting::set('alipay.partner_id', $data['alipay_partner']);
        Setting::set('alipay.seller_id', $data['alipay_account']);
        Setting::set('alipay-mobile.sign_type', 'RSA');
        Setting::set('alipay-mobile.private_key_path', storage_path() . '/cert/private_key.pem');
        Setting::set('alipay-mobile.public_key_path', storage_path() . '/cert/public_key.pem');
        Setting::set('alipay-mobile.notify_url', Url::shopSchemeUrl('payment/alipay/notifyUrl.php'));
        Setting::set('alipay-web.key', $data['alipay_secret']);
        Setting::set('alipay-web.sign_type', 'MD5');
        Setting::set('alipay-web.notify_url', Url::shopSchemeUrl('payment/alipay/notifyUrl.php'));
        Setting::set('alipay-web.return_url', Url::shopSchemeUrl('payment/alipay/returnUrl.php'));
    }


    private function  upload($fileinput)
    {
        $valid_ext = ['pem'];

            $file = \Request::file($fileinput);
            if ($file->isValid()) {

                // 获取文件相关信息
                $originalName = $file->getClientOriginalName(); // 文件原名
                $ext = $file->getClientOriginalExtension();     // 扩展名
                $realPath = $file->getRealPath();   //临时文件的绝对路径
                $i = \YunShop::app()->uniacid;

                $upload_file = $i . '_' . $originalName;

                if (!in_array($ext, $valid_ext)) {
                    return ['status' => -1];
                }

                $bool = \Storage::disk('cert')->put($upload_file, file_get_contents($realPath));

                return $bool ? ['status' => 1, 'file' => $originalName] : ['status' => 0];
            }
    }

    /**
     * 前端入口二维码
     *
     * @return string
     */
    public function entry()
    {
        return view('setting.shop.entry', [

        ])->render();
    }

    private function updateFile($file)
    {
        $data = [];
        $file = array_keys($file)[0];
        $update = $this->upload($file);

        if ($update['status'] == -1 || $update['status'] == 0) {
            return $update;
        }

        if ($update['status'] == 1) {
            $uniacid = \YunShop::app()->uniacid;
            $data[$file] = storage_path('cert/' . $uniacid . "_" . $update['file']);
        }
        if (!empty($data)) {
            return ['status' => 1, 'data' => $data];
        }

        return null;
    }

    private function updateFileV2($file_data)
    {
        $data = [];
        $uniacid = \YunShop::app()->uniacid;
        $file_suffix = '.pem';
        foreach ($file_data as $key => $value) {
            $file_name = $uniacid."_".$key.$file_suffix;
            $bool = \Storage::disk('cert')->put($file_name, $value);

            if ($bool) {
                $data[$key] = storage_path('cert/' . $file_name);
            } else {
                return ['status' => 0];
            }

        }

        if (!empty($data)) {
            return ['status' => 1, 'data' => $data];
        }

        return null;
    }

    /**
     * 设置分享默认值
     */
    public function shareDefault() {
        $share = \Setting::get('shop.share');
        if (!$share) {
            $requestModel = [
                'follow_url' => '',
                'title' => '',
                'icon' => '',
                'desc' => '',
                'url' => ''
            ];
            \Setting::set('shop.share', $requestModel);
        }
    }
    /**
     * 设置物流查询
     */

    public function expressInfo()
    {
        if(request()->ajax()){
            $set = Setting::get('shop.express_info');//快递鸟1002状态为免费，8001状态为收费

            $requestModel = request()->express_info;
            if ($requestModel) {
                if (Setting::set('shop.express_info', $requestModel)) {
                    return $this->successJson(' 物流查询信息设置成功');
                } else {
                    return $this->errorJson('物流查询信息设置失败');
                }
            }
            return $this->successJson('请求接口成功',  [
                'set' => $set,
            ]);
        }
        return view('setting.shop.express_info');
    }

    /**
     * 检查是否存在邀请码
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkInviteCode(){
        $data = request()->invite_code ?: '';
        if($data){
            if(MemberShopInfo::where('invite_code', '=', request()->invite_code)->count() > 0) {
                return $this->successJson('请求接口成功',  [
                    'data' => 1
                ]);
            }else{
                return $this->errorJson('默认邀请码有误，请重新输入',[
                    'data' => 2//不存的邀请码
                ]);
            }
        }

        return $this->successJson('请求接口成功',  [
            'data' => 0
        ]);

    }

}