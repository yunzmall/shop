<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/8/25 下午1:51
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\frontend\modules\from\controllers;


use app\common\components\ApiController;
use app\common\models\Goods;
use app\common\models\GoodsCategory;
use app\common\models\order\OrderInvoice;
use app\common\services\DivFromService;
use app\common\services\IDCardService;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\common\models\MemberCertified;
use Yunshop\Invoice\models\InvoiceCategory;
use Yunshop\Invoice\models\InvoiceGoods;
use Yunshop\Invoice\models\InvoiceRelation;
use Yunshop\Invoice\models\InvoiceRise;

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
    /**
     * 商品表单规则说明
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliveryTime(Request $request, $integrated = null)
    {
        $delivery_time = \Setting::get('shop.trade.delivery_time')  ? 1 : 0;
        $deliveryTime = [
            'status' => $delivery_time,
            'time' => [
                '9:00-10:00',
                '10:00-11:00',
                '11:00-12:00',
                '12:00-13:00',
                '13:00-14:00',
                '14:00-15:00',
                '15:00-16:00',
                '16:00-17:00',
                '17:00-18:00',
                '18:00-19:00',
                '19:00-20:00',
                '20:00-21:00',
                '21:00-22:00',
                '22:00-23:00',
                '23:00-00:00',
                '00:00-01:00',
                '01:00-02:00',
                '02:00-03:00',
                '03:00-04:00',
				'04:00-05:00',
				'05:00-06:00',
				'06:00-07:00',
				'07:00-08:00',
				'08:00-09:00',
                ]
        ];
        if (is_null($integrated)){
            return $this->successJson('ok',$deliveryTime );
        }else{
            return show_json(1,$deliveryTime);
        }

    }

    //判断是否开启发票
    public function isinvoice(Request $request, $integrated = null)
    {
        $mid = \YunShop::app()->getMemberId();
        $setting = \Setting::get('plugin.invoice');//新发票设置
        //发票类型
        $type = [
            0 => '电子发票',
            1 => '纸质发票',
            2 => '专用发票',
        ];
        //发票内容
        $content_type = [
            0 => '商品明细',
            1 => '商品类型',
        ];
        $invoice_type = $content = $rises = [];
        $tax_rate = 0;

        if (app('plugins')->isEnabled('invoice') && $setting['is_open']) {
            $is_notice = $is_content = 1;
            $rise = InvoiceRise::uniacid()
                ->where('member_id', $mid)
                ->get();
            $rise = $rise->isEmpty() ? [] : $rise->toArray();
            foreach ($rise as $value) {
                $default_rise = [
                    'collect_name' => $value['collect_name'],
                    'gmf_taxpayer' => $value['tax_num'],
                    'gmf_bank_admin' => $value['bank_admin'],
                    'gmf_address' => $value['address'],
                    'gmf_bank' => $value['bank'],
                    'gmf_mobile' => $value['mobile'],
                    'col_address' => $value['address'],
                    'email' => $value['email']
                ];

                if ($value['type'] == '0') {
                    $rises['personal'][] = $value;
                    $value['is_default'] == 1 && $personal_default_rise = $default_rise;
                }
                if ($value['type'] == '1') {
                    $rises['company'][] = $value;
                    $value['is_default'] == 1 && $unit_default_rise = $default_rise;
                }
            }
            $tax_rate = $this->goTaxRate() ?: 0;

            foreach ($setting['content'] as $value) {
                $content[] = [
                    'name' => $content_type[$value],
                    'value' => $value
                ];
            }

            foreach ($content as $key => $item) {
                if (!$item['name']) {
                    unset($content[$key]);
                }
            }


            foreach ($setting['invoice_type'] as $value) {
                $invoice_type[] = [
                    'name' => $type[$value],
                    'value' => $value
                ];
            }

            foreach ($invoice_type as $key => $item) {
                if (!$item['name']) {
                    unset($invoice_type[$key]);
                }
            }


        } else {
            $is_notice = $is_content = 0;
            //旧发票(商城设置)
            $trade = \Setting::get('shop.trade');
            $trade_invoice_set = [
                //电子
                0 => [
                    'type_name' => $type[0],
                    'is_open' => $trade['invoice']['electron'] ?? 0
                ],
                //纸质
                1 => [
                    'type_name' => $type[1],
                    'is_open' => $trade['invoice']['papery'] ?? 0
                ],
            ];

            foreach ($trade_invoice_set as $key => $value) {
                if ($value['is_open']) {
                    $invoice_type[] = [
                        'name' => $value['type_name'],
                        'value' => $key
                    ];
                }
            }

            foreach ($invoice_type as $key => $item) {
                if (!$item['name']) {
                    unset($invoice_type[$key]);
                }
            }
        }

//        $sql_select = ['id', 'collect_name', 'email', 'gmf_taxpayer as tax_num', 'gmf_bank_admin as bank_admin', 'gmf_address as address', 'gmf_bank as bank', 'gmf_mobile as mobile', 'col_address', 'col_name', 'col_mobile', 'content'];
        $sql_select = ['id', 'collect_name', 'email', 'gmf_taxpayer', 'gmf_bank_admin', 'gmf_address', 'gmf_bank', 'gmf_mobile', 'col_address', 'col_name', 'col_mobile', 'content'];
        $sqlData = [
            'personal' => [
                'where' => ['rise_type' => 1],
            ],
            'unit' => [
                'where' => ['rise_type' => 0],
            ],
            'special' => [
                'where' => ['invoice_type' => 2],
            ],
        ];

        //最近订单发票
        $default_rise = [];
        foreach ($sqlData as $key => $value) {
            if (in_array($key,['personal','unit'])) {
                $default_rise[$key] = OrderInvoice::uniacid()->select($sql_select)->where($value['where'])->where('invoice_type','!=',2)->where('uid',$mid)->orderBy('created_at','desc')->first();
            } else {
                $default_rise[$key] = OrderInvoice::uniacid()->select($sql_select)->where($value['where'])->where('uid',$mid)->orderBy('created_at','desc')->first();
            }
        }

//        //收票人信息
//        $default_rise['taker_info'] = [
//            //电子
//            'electronic' => OrderInvoice::uniacid()->select('col_mobile', 'email')->where('invoice_type', 0)->orderBy('created_at','desc')->first(),
//            //纸质
//            'paper' => OrderInvoice::uniacid()->select('col_name', 'col_mobile', 'col_address')->where('invoice_type', 1)->orderBy('created_at', 'desc')->first(),
//            //专用
//            'special' => OrderInvoice::uniacid()->select('col_name', 'col_mobile', 'col_address')->where('invoice_type', 2)->orderBy('created_at','desc')->first(),
//        ];

        !empty($personal_default_rise) && $default_rise['personal'] = $personal_default_rise;
        !empty($unit_default_rise) && $default_rise['unit'] = $unit_default_rise;

        $result = [
//            'is_open' => empty($setting['is_open']) ? 0 : $setting['is_open'],
            'is_open' => !empty($invoice_type) ? 1 : 0,
            'invoice_type' => $invoice_type,
            'content' => $content,
            'is_notice' => $is_notice,//1-显示发票须知，0-不显示
            'is_content' => $is_content,//1-显示发票内容，0-不显示
            'notice' => empty($setting['notice']) ? '' : $setting['notice'],
            'rises' => $rises,
            'tax_rate' => $tax_rate,
            'default_rise' => $default_rise,
//            'default_rise' => array_filter($default_rise),
        ];
        return show_json(1, $result);
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

        //运费说明
        $this->dataIntegrated($this->getFreightExplain($request,true),'freightExplain');
        //配送时间
        $this->dataIntegrated($this->deliveryTime($request,true),'deliveryTime');
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

    private function getFreightExplain(Request $request, $integrated = null)
    {
        $shopOrderSet = \Setting::get('shop.order');

        $freightExplainSet = [
            'is_freight_explain' => $shopOrderSet['is_freight_explain'] ?? 0,
            'freight_explain_content' => explode("\n",$shopOrderSet['freight_explain_content']) ?? []
        ];

        if (is_null($integrated)) {
            return $this->successJson('ok',$freightExplainSet);
        } else {
            return show_json(1,$freightExplainSet);
        }
    }

    private function goTaxRate()
    {
        $goods_id = request()->input('goods_id');
        $good_tax_rate = InvoiceRelation::uniacid()->where('goods_id', $goods_id)->pluck('tax_rate');
        $good_tax_rate = empty($good_tax_rate) ? [] : array_unique($good_tax_rate->toArray());
        if (!$good_tax_rate) {
            $category_id = GoodsCategory::where('goods_id', $goods_id)->pluck('category_id');
            $category_id = empty($category_id) ? [] : $category_id->toArray();
            if ($category_id) {
                $good_tax_rate = InvoiceCategory::uniacid()->where('category_id', $category_id)->pluck('tax_rate');
                $good_tax_rate = empty($good_tax_rate) ? [] : array_unique($good_tax_rate->toArray());
            }
        }
        return $good_tax_rate;
    }
}
