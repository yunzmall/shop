<?php
namespace app\backend\modules\coupon\controllers;

use app\backend\modules\coupon\models\HotelCoupon;
use app\common\components\BaseController;
use app\backend\modules\coupon\models\Coupon;
use app\common\helpers\Cache;
use app\common\helpers\PaginationHelper;
use app\common\models\MemberCoupon;
use app\common\helpers\Url;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\coupon\models\CouponLog;
use app\backend\modules\goods\models\Goods;
use app\backend\modules\goods\models\Category;
use app\common\facades\Setting;
use app\common\models\Store;
use app\common\services\ExportService;
use app\frontend\modules\coupon\listeners\CouponSend;
use Carbon\Carbon;
use Yunshop\Hotel\common\models\CouponHotel;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/20
 * Time: 16:20
 */
class CouponController extends BaseController
{
    //优惠券列表
    public function index()
    {
        if(isset(request()->laststock)){
            //系统通知详情进来，查询剩余数量为0的优惠券
            return $this->searchNotStockCoupon();
        }
        return view('coupon.index')->render();
    }

    //查询剩余数量为0的优惠券
    public function searchNotStockCoupon()
    {
        $pageSize = 10;
        $allData = Coupon::uniacid()->pluginId()
            ->where('total','>',-1)//非无限领取
            ->get()
            ->toArray();

        if(!empty($allData)){
            $id = [];
            foreach ($allData as $k=>$v){
                $get_total = MemberCoupon::uniacid()->where("coupon_id", $v['id'])->count();
                if(($v['total'] - $get_total) <= 0){
                    $id[] = $v['id'];
                }
            }
            if(!empty($id)){
                $list = Coupon::uniacid()->pluginId()
                    ->whereIn('id',$id)
                    ->orderBy('display_order', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->paginate($pageSize)->toArray();
                $pager = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);
                foreach ($list['data'] as &$item) {
                    $item['gettotal'] = MemberCoupon::uniacid()->where("coupon_id", $item['id'])->count();
                    $item['usetotal'] = MemberCoupon::uniacid()->where("coupon_id", $item['id'])->where("used", 1)->count();
                    $lasttotal = $item['total'] - $item['gettotal'];
                    $item['lasttotal'] = ($lasttotal > 0) ? $lasttotal : 0; //考虑到可领取总数修改成比之前的设置小, 则会变成负数
                }
                return view('coupon.index', [
                    'list' => $list['data'],
                    'pager' => $pager,
                    'total' => $list['total'],
                ])->render();
            }
        }
        return view('coupon.index', [
            'list' => [],
            'pager' => '',
            'total' => 0,
        ])->render();
    }


    public function couponData()
    {
        $keyword = request()->keyword;
        $getType = request()->gettype;
        $timeSearchSwitch = request()->timesearchswtich;
        $timeStart = request()->time['start'];
        $timeEnd = request()->time['end'];
        $pageSize = 10;
        if (empty($keyword) && (empty($getType) && $getType == '') && ($timeSearchSwitch == 0)) {
            $list = Coupon::uniacid()->pluginId()->orderBy('display_order', 'desc')->orderBy('updated_at', 'desc')->paginate($pageSize)->toArray();
        } else {
            $list = Coupon::getCouponsBySearch($keyword, $getType, $timeSearchSwitch, $timeStart, $timeEnd)
                ->pluginId()
                ->orderBy('display_order', 'desc')
                ->orderBy('updated_at', 'desc')
                ->paginate($pageSize)
                ->toArray();
        }
        foreach ($list['data'] as &$item) {
            $item['gettotal'] = MemberCoupon::uniacid()->where("coupon_id", $item['id'])->count();
            $item['usetotal'] = MemberCoupon::uniacid()->where("coupon_id", $item['id'])->where("used", 1)->count();
            $lasttotal = $item['total'] - $item['gettotal'];
            $item['lasttotal'] = ($lasttotal > 0) ? $lasttotal : 0; //考虑到可领取总数修改成比之前的设置小, 则会变成负数
        }

        $data = [
            'list' => $list,
            'total' => $list['total'],
        ];
        return $this->successJson('ok',$data);
    }

    //添加优惠券
    public function create()
    {
        //获取表单提交的值
        $couponRequest = request()->coupon;
        $couponRequest['uniacid'] = \YunShop::app()->uniacid;
        $couponRequest['time_start'] = request()->time['start'];
        $couponRequest['time_end'] = request()->time['end'];
        $couponRequest['category_ids'] = request()->category_ids;
        $couponRequest['categorynames'] = request()->category_names;
        $couponRequest['goods_ids'] = request()->goods_id ?: request()->goods_ids;
        $couponRequest['goods_names'] = request()->goods_name ?: request()->goods_names;
        //新增门店
        $couponRequest['storeids'] = request()->store_ids; //去重,去空值
        $couponRequest['storenames'] = request()->store_names;
        $hotel_is_open = app('plugins')->isEnabled('hotel');
        //获取会员等级列表
        $memberLevels = MemberLevel::getMemberLevelList();
        //表单验证
        if (request()->coupon) {
            if (request()->goods_id) {
                if (count($couponRequest['goods_id']) > 1) {
                    return $this->errorJson('优惠券创建失败,兑换券只能指定一个商品');
                }
            }
            $coupon = new HotelCoupon();
            if ($hotel_is_open) {
                $coupon->widgets['more_hotels'] = request()->hotel_ids;
            }
            $couponRequest['use_conditions'] = serialize($couponRequest['use_conditions']);
            if ($couponRequest['time_limit'] == 1) {
                //使用时间范围时
                $couponRequest['time_end'] = strtotime(date('Y-m-d 23:59:59',$couponRequest['time_end']));
            }
            $coupon->fill($couponRequest);
            $validator = $coupon->validator();
            if ($validator->fails()) {
                $this->errorJson($validator->messages());
            } elseif ($coupon->save()) {
                return $this->successJson('优惠券创建成功');
            } else {

                $this->errorJson('优惠券创建失败');
            }
        }
        $store_is_open = app('plugins')->isEnabled('store-cashier');
        $couponRequest['use_conditions'] = unserialize($couponRequest['use_conditions']);
        $data = [
            'coupon' => $couponRequest,
            'memberlevels' => $memberLevels,
            'timestart' => strtotime(\YunShop::request()->time['start']),
            'timeend' => strtotime(\YunShop::request()->time['end']),
            'hotel_is_open' => $hotel_is_open,
            'store_is_open'    => $store_is_open,
        ];
        return $this->successJson('ok',$data);
    }

    //编辑优惠券
    public function edit()
    {
        $coupon_id = intval(request()->id);
        if (!$coupon_id) {
            $this->errorJson('请传入正确参数.');
        }
        //获取会员等级列表
        $memberLevels = MemberLevel::getMemberLevelList();
        $coupon = HotelCoupon::getCouponById($coupon_id);
        if (!empty($coupon->goods_ids)) {
            $coupon->goods_ids = array_filter(array_unique($coupon->goods_ids)); //去重,去空值
            if (!empty($coupon->goods_ids)) {
                $coupon->goods_names = Goods::getGoodNameByGoodIds($coupon->goods_ids); //因为商品名称可能修改,所以必须以商品表为准 //todo category_names和goods_names是不可靠的, 考虑删除这2个字段
            }
        }
        if (!empty($coupon->category_ids)) {
            $ids = $coupon->category_ids;
            $categorynames = Category::uniacid()//因为商品分类名称可能修改,所以必须以商品表为准
                ->select('id','name')
                ->whereIn('id', $ids)
                ->orderByRaw(DB::raw("FIELD(id, ".implode(',', $ids).')')) //必须按照categoryIds的顺序输出分类名称
                ->get();
            $ids = $categorynames->pluck('id')->toArray();
            $names = $categorynames->pluck('name')->toArray();
        }
        //新增酒店
        $hotel_is_open = app('plugins')->isEnabled('hotel');
        $couponRequest = request()->coupon;

        // 查询优惠券是否有被发放
        $memberCoupon = MemberCoupon::uniacid()->where("coupon_id", $coupon->id)->first();
        // 当存在发放优惠券并且用户编辑了发放总数,返回提示警告(中间 $couponRequest 不能被优化掉.否则前端拿不到优惠券基本信息)
        if ($memberCoupon && $couponRequest && $couponRequest['total'] != $coupon->total) {
            $this->errorJson('优惠券已经发放,无法对发放总数进行编辑');
        }

        if ($couponRequest) {
            if ($couponRequest['use_type'] == 8) {
                if (count(request()->goods_id) > 1) {
                    return $this->message('优惠券修改失败,兑换券只能指定一个商品');
                }
                $goodsIds = request()->goods_id;
                $goodsNames = request()->goods_name;
            }
            $couponRequest['time_start'] = request()->time['start'];
            $couponRequest['time_end'] = request()->time['end'];
            $coupon->use_type = request()->usetype;
            $coupon->category_ids = array_filter(array_unique(request()->category_ids)); //去重,去空值
            $coupon->categorynames = request()->category_names;
            $coupon->goods_ids = $goodsIds ? $goodsIds : array_filter(array_unique(request()->goods_ids)); //去重,去空值
            $coupon->goods_names = $goodsNames ? $goodsNames : request()->goods_names;
            //新增门店
            $coupon->storeids = array_filter(array_unique(request()->store_ids)); //去重,去空值
            $coupon->storenames = request()->store_names;
            if ($hotel_is_open) {
                $coupon->widgets['more_hotels'] = request()->hotel_ids;
            }
            //表单验证
            $couponRequest['use_conditions'] = serialize($couponRequest['use_conditions']);
            $coupon->fill($couponRequest);
            $validator = $coupon->validator();
            if ($validator->fails()) {
                $this->errorJson($validator->messages());
            } else {
                if ($coupon->save()) {
                    //已失效的优惠券重新改为可使用
                    if ($coupon['time_limit'] == Coupon::COUPON_DATE_TIME_RANGE && time() < strtotime($coupon['time_end'])) {
                        MemberCoupon::where('coupon_id', $coupon['id'])->where('is_expired', 1)->update(['is_expired' => 0]);
                    }
                    if ($coupon['time_limit'] == Coupon::COUPON_SINCE_RECEIVE && ($coupon['time_days'] !== 0)) {
                        $member_coupon = MemberCoupon::where('coupon_id', $coupon['id'])->where('is_expired', 1)->get();
                        foreach ($member_coupon as $memberCoupon) {
                            if (time() < Carbon::createFromTimestamp(strtotime($memberCoupon['get_time']) + $coupon['time_days'] * 3600 * 24)->endOfDay()->timestamp) {
                                $memberCoupon->is_expired = 0;
                                $memberCoupon->save();
                            }
                        }
                    }
                    //店铺装修清除缓存
                    if (app('plugins')->isEnabled('designer')) {
                        Cache::flush();//清除缓存
                    }
                    return $this->successJson('优惠券修改成功');
                } else {
                    $this->errorJson('优惠券修改失败');
                }
            }
        }
        $coupon['use_conditions'] = unserialize($coupon['use_conditions']);
        $store_is_open = app('plugins')->isEnabled('store-cashier');
        $data = [
            'coupon' => $coupon->toArray(),
            'usetype' => $coupon->use_type,
            'category_ids' => $ids,
            'category_names' => $names,
            'goods_ids' => $coupon->goods_ids,
            'goods_names' => $coupon->goods_names,
            'memberlevels' => $memberLevels,
            'timestart' => $coupon->time_start->timestamp,
            'timeend' => $coupon->time_end->timestamp,
            'hotel_is_open' => $hotel_is_open,
            'hotel_ids' => $hotel_is_open ? CouponHotel::getHotels($coupon_id) : [],
            'store_ids' => $coupon->storeids,
            'store_names' =>$coupon->storenames,
            'store_is_open' => $store_is_open,
            'goods' => Goods::select(['id', 'title'])->uniacid()->whereIn('id', $coupon['use_conditions']['good_ids'])->get(),
            'store' => app('plugins')->isEnabled('store-cashier') ? Store::select(['id', 'store_name'])->uniacid()->whereIn('id', $coupon['use_conditions']['store_ids'])->get() : [],
        ];
        return $this->successJson('ok',$data);
    }

    public function couponView()
    {
        return view('coupon.coupon', [
            'id' => request()->id,
        ])->render();
    }

    //删除优惠券
    public function destory()
    {
        $coupon_id = intval(request()->id);
        if (!$coupon_id) {
            $this->errorJson('请传入正确参数.');
        }

        $coupon = Coupon::getCouponById($coupon_id);
        if (!$coupon) {
            return $this->errorJson('无此记录或者已被删除.');
        }

        $usageCount = Coupon::getUsageCount($coupon_id)->first()->toArray();
        if ($usageCount['has_many_member_coupon_count'] > 0) {
            return $this->errorJson('优惠券已被领取且尚未使用,因此无法删除');
        }

        $res = HotelCoupon::deleteCouponById($coupon_id);
        if ($res) {
            //店铺装修清除缓存
            if (app('plugins')->isEnabled('designer')) {
                Cache::flush();//清除缓存
            }
            return $this->successJson('删除优惠券成功');
        } else {
            return $this->errorJson('删除优惠券失败');
        }
    }


    /**
     * 获取搜索优惠券
     * @return html
     */
    public function getSearchCoupons()
    {
        $keyword = \YunShop::request()->keyword;
        $coupons = Coupon::getCouponsByName($keyword);
        return view('coupon.query', [
            'coupons' => $coupons
        ])->render();
    }


    public function getSearchCouponsV2()
    {
        $keyword = request()->keyword;
        $coupons = Coupon::getCouponsByName($keyword);
        return $this->successJson('ok',$coupons);
    }


    /**
     * 获取搜索优惠券
     * @return html
     */
    public function getVueSearchCoupons()
    {
        $keyword = \YunShop::request()->keyword;
        $coupons = Coupon::getCouponsByName($keyword);
        echo json_encode($coupons);
    }

    //用于"适用范围"添加商品或者分类
    public function addParam()
    {
        $type = \YunShop::request()->type;
        switch ($type) {
            case 'goods':
                return view('coupon.tpl.goods')->render();
                break;
            case 'category':
                return view('coupon.tpl.category')->render();
                break;
            case 'store':
                return view('coupon.tpl.store')->render();
                break;
            case 'hotel':
                return view('coupon.tpl.hotel')->render();
                break;
            case 'goods-exchange':
                return view('coupon.tpl.exchange-goods')->render();
                break;
        }
    }

    //优惠券领取和发放记录
    public function log()
    {
        $couponId = request()->id;
        $couponName = request()->couponname;
        $nickname = request()->nickname;
        $getFrom = request()->getfrom;
        $searchSearchSwitch = request()->timesearchswtich;
        $timeStart = request()->time['start'];
        $timeEnd = request()->time['end'];

        if (empty($couponId) && empty($couponName) && ($getFrom == null) && empty($nickname) && ($searchSearchSwitch == 0)) {
            $list = CouponLog::getCouponLogs();
        } else {
            $searchData = [];
            if (!empty($couponId)) {
                $searchData['coupon_id'] = $couponId;
            }
            if (!empty($couponName)) {
                $searchData['coupon_name'] = $couponName;
            }
            if (!empty($nickname)) {
                $searchData['nickname'] = $nickname;
            }
            if ($getFrom != '') {
                $searchData['get_from'] = $getFrom;
            }
            if ($searchSearchSwitch == 1) {
                $searchData['time_search_swtich'] = $searchSearchSwitch;
                $searchData['time_start'] = $timeStart;
                $searchData['time_end'] = $timeEnd;
            }
            $list = CouponLog::searchCouponLog($searchData);
        }

        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());

        $data = [
            'list' => $list,
            'couponid' => $couponId,
        ];
        return $this->successJson('ok',$data);
//        return view('coupon.log', [
//            'list' => $list,
//            'pager' => $pager,
//            'couponid' => $couponId,
//        ])->render();
    }

    public function logView()
    {
        return view('coupon.log', [
            'id' => request()->id,
        ])->render();
    }

    public function export()
    {
        $couponId = request()->id;
        $couponName = request()->couponname;
        $nickname = request()->nickname;
        $getFrom = request()->getfrom;
        $searchSearchSwitch = request()->timesearchswtich;
        $timeStart = request()->start;
        $timeEnd = request()->end;
        $export_page = request()->export_page ? request()->export_page : 1;

        if (empty($couponId) && empty($couponName) && ($getFrom == null) && empty($nickname) && ($searchSearchSwitch == 0)) {
            $buidler = CouponLog::getCouponLogsBuilder();
        } else {
            $searchData = [];
            if (!empty($couponId)) {
                $searchData['coupon_id'] = $couponId;
            }
            if (!empty($couponName)) {
                $searchData['coupon_name'] = $couponName;
            }
            if (!empty($nickname)) {
                $searchData['nickname'] = $nickname;
            }
            if ($getFrom != '') {
                $searchData['get_from'] = $getFrom;
            }
            if ($searchSearchSwitch == 1) {
                $searchData['time_search_swtich'] = $searchSearchSwitch;
                $searchData['time_start'] = $timeStart;
                $searchData['time_end'] = $timeEnd;
            }
            $buidler = CouponLog::searchCouponLogBuilder($searchData);
        }
        $export_model = new ExportService($buidler, $export_page);
        $file_name = date('Ymdhis', time()) . '优惠券领取记录';
        $export_data[0] = ['ID', '优惠券名称', '用户', '获得途径', '领取时间', '日志详情'];
        foreach ($export_model->builder_model as $key => $item) {
            $type = '';
            switch ($item->getfrom)
            {
                case '0' :
                    $type = '发放';
                    break;
                case '1' :
                    $type = '领取';
                    break;
                case '4' :
                    $type = '购物赠送';
                    break;
                case '5' :
                    $type = '会员转赠';
                    break;
                case '6' :
                    $type = '签到奖励';
                    break;
            }
            $export_data[$key + 1] = [
                $item->id,
                $item->coupon->name,
                $item->member->nickname,
                $type,
                date('Y-m-d h:i:s',$item->createtime),
                $item->logno,
            ];
        }
        $export_model->export($file_name, $export_data, \Request::query('route'));
    }



}
