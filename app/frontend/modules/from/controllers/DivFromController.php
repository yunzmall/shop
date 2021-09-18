<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/8/25 下午1:51
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\frontend\modules\from\controllers;


use app\common\components\ApiController;
use app\common\services\DivFromService;
use app\common\services\IDCardService;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\common\models\MemberCertified;

class DivFromController extends ApiController
{
    /**
     * 商品表单是否显示
     * @return \Illuminate\Http\JsonResponse
     */
    public function isDisplay(Request $request, $integrated = null)
    {
        $goods_ids = json_decode(request()->input('goods_ids'),true);


        if (!is_array($goods_ids)) {
            if(is_null($integrated)){
                return $this->errorJson('未获取到商品ID集');
            }else{
                return show_json(0,'未获取到商品ID');
            }
        }
        $status = DivFromService::isDisplay($goods_ids,\YunShop::app()->getMemberId());
        if(is_null($integrated)){
            return $this->successJson('ok',['status'=> $status,'member_status'=>DivFromService::getMemberStatus(\YunShop::app()->getMemberId())]);
        }else{
            return show_json(1,['status'=> $status,'member_status'=>DivFromService::getMemberStatus(\YunShop::app()->getMemberId())]);
        }

    }

    /**
     * 商品表单规则说明
     * @return \Illuminate\Http\JsonResponse
     */
    public function explain(Request $request, $integrated = null)
    {
        $explain = array_pluck(\Setting::getAllByGroup('div_from')->toArray(), 'value', 'key');
        if (is_null($integrated)){
            return $this->successJson('ok',$explain );
        }else{
            return show_json(1,$explain);
        }

    }

    //判断是否开启发票
    public function isinvoice(Request $request, $integrated = null)
    {

        $trade = \Setting::get('shop.trade');
        $invoice['papery'] = $trade['invoice']['papery']!=0 ? $trade['invoice']['papery'] :0;
        $invoice['electron'] = $trade['invoice']['electron']!=0 ? $trade['invoice']['electron'] :0;
        if(is_null($integrated)){
            return $this->successJson('ok',['invoice'=>$invoice]);
        }else{
            return show_json(1,['invoice'=>$invoice]);
        }

    }

    /**
     * 修改会员真实姓名、身份证ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMemberInfo()
    {
        $member_name = \YunShop::request()->member_name;
        if (!$member_name) {
            return $this->errorJson('会员真实名称不能为空');
        }

        $member_card = \YunShop::request()->member_card;
        if (!$member_card) {
            return $this->errorJson('会员身份证号码不能为空');
        }
        if (!IDCardService::isCard($member_card)) {
            return $this->errorJson('请输入正确的身份证号码');
        }

        if (!\YunShop::app()->getMemberId()) {
            return $this->errorJson('未获取到会员ID');
        }
        Member::where('uid', \YunShop::app()->getMemberId())->update(['realname' => $member_name, 'idcard' => $member_card]);
        MemberCertified::insertData(array(
            'uniacid' => \YunShop::app()->uniacid,
            'member_id' => \YunShop::app()->getMemberId(),
            'realname' => $member_name,
            'idcard' => $member_card,
            'remark' => '表单修改实名信息',
            'created_at' => time(),
            'updated_at' => time()
        ));

        return $this->successJson('ok');
    }

    public function getMemberInfo(Request $request, $integrated = null)
    {
        $MemberInfo = DivFromService::getMemberCardAndName(\YunShop::app()->getMemberId());
        if (!$MemberInfo) {
            if(is_null($integrated)){
                return $this->errorJson('未获取到会员信息！！');
            }else{
                return show_json(1,'未获取到会员信息！！');
            }

        }
        if(is_null($integrated)){
            return $this->successJson('ok',$MemberInfo);
        }else{
            return show_json(1,$MemberInfo);
        }

    }
    private function getCouponSet(Request $request, $integrated = null)
    {
        $couponSet = \Setting::get('shop.coupon')['coupon_remind'] ?: '0';
        if(is_null($integrated)){
            return $this->successJson('ok',$couponSet);
        }else{
            return show_json(1,$couponSet);
        }

    }
    public function getParams(Request $request)
    {
        //判断是否开启发票
        $this->dataIntegrated($this->isinvoice($request, true), 'sinvoice');
        //商品表单是否显示
        $this->dataIntegrated($this->isDisplay($request, true), 'isDisplay');
        //获取会员详情
        $this->dataIntegrated($this->getMemberInfo($request, true), 'getMemberInfo');
        //商品表单规则说明
        $this->dataIntegrated($this->explain($request, true), 'explain');
        //共享链支付协议
        $this->dataIntegrated(\app\frontend\modules\shop\controllers\IndexController::getPayProtocol($request, true),'getPayProtocol');

        //判断是否开启下单提示使用优惠券
        $this->dataIntegrated($this->getCouponSet($request, true), 'getCouponSet');
        $this->dataIntegrated($this->getStoreSearch($request, true), 'storeSearch');
        return $this->successJson('', $this->apiData);
    }

    private function getStoreSearch(Request $request, $integrated = null)
    {
        $data=[];
        if(app('plugins')->isEnabled('store-search'))
        {
            $store_search_set = \Setting::get('plugin.store-search');
            if($store_search_set['is_open_but'])
            {
                $data =[
                    'is_open_but'=>$store_search_set['is_open_but'],
                    'but_title'=> $store_search_set['but_title'],
                    'web_url'=>$store_search_set['web_url'],
                    'app_url'=>$store_search_set['app_url'],
                ];

            }
        }
        if(is_null($integrated))
        {
            return $this->successJson('ok',$data);
        }
        else
        {
            return show_json(1,$data);
        }

    }
}