<?php

/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/3/8
 * Time: 上午10:11
 */

namespace app\backend\modules\member\controllers;

use app\backend\modules\member\models\Member;
use app\backend\modules\member\models\MemberRelation;
use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\member\services\FansItemService;
use app\common\components\BaseController;
use app\backend\modules\member\models\MemberRelation as Relation;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\Goods;
use app\common\models\notice\MessageTemp;
use app\common\services\ExportService;
use Illuminate\Database\Eloquent\Collection;


class MemberRelationController extends BaseController
{
    public $pageSize = 20;

    /**
     * 加载模板
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        return view('member.relation', [])->render();
    }

    /**
     * 列表
     * @return string
     * @throws \Throwable
     */
    public function show()
    {
        $relation = Relation::uniacid()->first();
        $setting = \Setting::get('member.relation');

        if (!empty($relation)) {
            $relation = $relation->toArray();
        }

        if (!empty($relation['become_term'])) {
            $relation['become_term'] = unserialize($relation['become_term']);
        }

        if (!empty($relation['become_goods'])) {
            $relation_goods = unserialize($relation['become_goods']);
            $goods_ids = [];
            foreach ($relation_goods as $item) {
                $goods_ids[] = $item['goods_id'];
            }
            // 查询当前未被删除的商品
            $current_goods = Goods::uniacid()->select('id', 'title', 'thumb')
                ->whereIn('id', $goods_ids)
                ->whereNull('deleted_at')
                ->get();
            if ($current_goods) {
                $current_goods = $current_goods->toArray();
                foreach ($current_goods as $key => $value) {
                    $current_goods[$key]['thumb'] = yz_tomedia($value['thumb']);
                }
                $goods = $current_goods;
            } else {
                $goods = [];
            }
        } else {
            $goods = [];
        }
        $relationship = [
            'status'              => $relation['status'],
            'become'              => $relation['become'],
            'become_term2'        => empty($relation['become_term'][2]) ? "" : 2,
            'become_ordercount'   => $relation['become_ordercount'],
            'become_term3'        => empty($relation['become_term'][3]) ? "" : 3,
            'become_moneycount'   => $relation['become_moneycount'],
            'become_term4'        => empty($relation['become_term'][4]) ? "" : 4,
            'goods'               => $goods,
            'is_sales_commission' => app('plugins')->isEnabled('sales-commission') ? 1 : 0,
            'become_term5'        => empty($relation['become_term'][5]) ? "" : 5,
            'become_selfmoney'    => $relation['become_selfmoney'],
            'become_order'        => $relation['become_order'],
            'become_child'        => $relation['become_child'],
            'become_check'        => $relation['become_check'],
        ];

        $reward = [
            'reward_points'  => $relation['reward_points'],
            'maximum_number' => $relation['maximum_number']
        ];

        $page = [
            'is_jump'          => $setting['is_jump'],
            'jump_link'        => $setting['jump_link'],
            'small_jump_link'  => $setting['small_jump_link'],
            'share_page'       => $relation['share_page'],
            'share_page_deail' => $relation['share_page_deail'],
        ];
        return $this->successJson('ok', [
            'relationship' => $relationship,
            'reward'       => $reward,
            'page'         => $page
        ]);
    }

    /**
     * 保存关系链数据
     *
     * @return mixed
     */
    public function save()
    {
        $setData = $this->setData(\YunShop::request()->setdata);
        $setting = \YunShop::request()->setting;
        if ($setting) {
            \Setting::set('member.relation', $setting);
        }
        $setData['uniacid'] = \YunShop::app()->uniacid;

        if (empty($setData['become_order'])) {
            $setData['become_order'] = 0;
        }

        if (empty($setData['become_ordercount'])) {
            $setData['become_ordercount'] = 0;
        }

        if (!empty($setData['become_term'])) {
            $setData['become_term'] = serialize($setData['become_term']);
        } else {
            $setData['become_term'] = '';
        }

        if (empty($setData['become_moneycount'])) {
            $setData['become_moneycount'] = 0;
        }

        $setData['become_goods_id'] = !empty($setData['become_goods_id']) ? implode(
            ',',
            $setData['become_goods_id']
        ) : 0;

        $setData['become_goods'] = !empty($setData['become_goods']) ? serialize($setData['become_goods']) : 0;
//        dd($setData['become_goods']);
        if (empty($setData['become_selfmoney'])) {
            $setData['become_selfmoney'] = 0;
        }

        $relation = Relation::uniacid()->first();

        if (!empty($relation)) {
            $relation->setRawAttributes($setData);
            (new \app\common\services\operation\RelationLog($relation, 'update'));
            $relation->save();
        } else {
            Relation::create($setData);
        }

        Cache::forget('member_relation');
        return $this->successJson('ok', ['data' => true]);
    }

    /**
     * 成为推广员 指定商品查询
     *
     * @return string
     */
    public function query()
    {
        $kwd = trim(\YunShop::request()->keyword);

        $goods_model = Goods::getGoodsByNameNew($kwd);

        if (!empty($goods_model)) {
            $data = $goods_model->toArray();

            foreach ($data['data'] as &$good) {
                $good['thumb'] = tomedia($good['thumb']);
            }
        } else {
            $data = [];
        }

        return $this->successJson('ok', $data);
    }

    /**
     * 加载模板  -- 资格申请
     * @return string
     * @throws \Throwable
     */
    public function apply()
    {
        return view('member.apply', [])->render();
    }

    /**
     * 会员资格申请列表
     *
     * @return string
     */
    public function applyShow()
    {
        $requestSearch = \YunShop::request()->search;
        $list = Member::getMembersToApply($requestSearch)
            ->paginate($this->pageSize)
            ->toArray();


        return $this->successJson('ok', [
            'list'          => (new FansItemService())->setFansItem($list),
            'total'         => $list['total'],
            'requestSearch' => $requestSearch
        ]);
    }

    /**
     * 申请协议
     *
     * @return mixed|string
     */
    public function applyProtocol()
    {
        $info = Setting::get("apply_protocol");

        $requestProtocol = \YunShop::request()->protocol;
        if ($requestProtocol) {
            $request = Setting::set('apply_protocol', $requestProtocol);
            if ($request) {
                return $this->message('保存成功', Url::absoluteWeb('member.member-relation.apply-protocol'));
            }
        }
        return $this->successJson('ok', ['info' => $info]);
    }

    public function base()
    {
        return view('member.relation-base', [])->render();
    }

    public function relationBase()
    {
        $info = \Setting::get('shop.relation_base');

        $base = \YunShop::request()->base;

        if ($base) {
            $request = Setting::set('shop.relation_base', $base);
            if ($request) {
                return $this->successJson('数据保存成功', $request);
            }
        }

        $temp_list = MessageTemp::getList();
        $notice = [
            'member_agent'     => $info['member_agent'],
            'member_new_lower' => $info['member_new_lower'],
        ];
        $member_relation = [
            'is_referrer'          => empty($info['is_referrer']) ? '0' : $info['is_referrer'],
            'parent_is_referrer'   => empty($info['parent_is_referrer']) ? '0' : $info['parent_is_referrer'],
            'is_recommend_wechat'  => empty($info['is_recommend_wechat']) ? '0' : $info['is_recommend_wechat'],
            'one_level'            => $info['relation_level'][0],
            'name1'                => $info['relation_level']['name1'],
            'two_level'            => $info['relation_level'][1],
            'name2'                => $info['relation_level']['name2'],
            'phone'                => $info['relation_level']['phone'],
            'realname'             => $info['relation_level']['realname'],
            'wechat'               => $info['relation_level']['wechat'],
            'is_statistical_goods' => $info['is_statistical_goods'],
            'statistical_goods'    => $info['statistical_goods']
        ];
        $member_merge = [
            'is_member_merge'     => $info['is_member_merge'],
            'is_merge_save_level' => empty($info['is_merge_save_level']) ? 0 : $info['is_merge_save_level']
        ];

        return $this->successJson('ok', [
            'banner'          => yz_tomedia($info['banner']),
            'notice'          => $notice,
            'temp_list'       => $temp_list,
            'member_relation' => $member_relation,
            'member_merge'    => $member_merge,
        ]);
    }

    /**
     * 验证是否开启默认模板
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIsDefaultById()
    {
        if (MessageTemp::uniacid()->where('id', request()->id)->where('is_default', 1)->first()) {
            return $this->successJson('ok', ['data' => true]);
        }
        return $this->successJson('ok', ['data' => false]);
    }

    /**
     * 检查审核
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function chkApply()
    {
        $id = \YunShop::request()->id;

        $member_shop_info_model = MemberShopInfo::getMemberShopInfo($id);

        if ($member_shop_info_model) {
            $member_shop_info_model->is_agent = 1;
            $member_shop_info_model->status = 2;

            if ($member_shop_info_model->inviter == 0) {
                $member_shop_info_model->inviter = 1;
            }

            if ($member_shop_info_model->save()) {
                Member::setMemberRelation($member_shop_info_model->member_id, $member_shop_info_model->parent_id);

                Relation::sendGeneralizeNotify($member_shop_info_model->member_id);

                return $this->successJson('审核通过', ['data' => true]);
            } else {
                return $this->errorJson('审核失败');
            }
        } else {
            return $this->errorJson('会员不存在');
        }
    }

    /**
     * 数据导出
     *
     */
    public function export()
    {
        $file_name = date('Ymdhis', time()) . '会员资格申请导出';

        $requestSearch = \YunShop::request()->search;

        $list = Member::getMembersToApply($requestSearch);
        $export_page = request()->export_page ? request()->export_page : 1;

        $export_model = new ExportService($list, $export_page);
        $file_name = date('Ymdhis', time()) . '会员导出' . $export_page;

        $export_data[0] = ['会员ID', '推荐人姓名', '粉丝姓名', '会员姓名', '手机号', '申请时间'];

        foreach ($list->get()->toArray() as $key => $item) {
            if (!empty($item['yz_member']) && !empty($item['yz_member']['agent'])) {
                $agent_name = $item['yz_member']['agent']['nickname'];
            } else {
                $agent_name = '';
            }

            $export_data[$key + 1] = [
                $item['uid'],
                $agent_name,
                $item['nickname'],
                $item['realname'],
                $item['mobile'],
                date('Y.m.d', $item['yz_member']['apply_time'])
            ];
        }
        // 此处参照商城订单管理的导出接口
        app('excel')->store(new \app\exports\FromArray($export_data), $file_name . '.xlsx', 'export');
        app('excel')->download(new \app\exports\FromArray($export_data), $file_name . '.xlsx')->send();
    }

    protected function setData($setData)
    {
        $setData['become'] = empty($setData['become']) ? 0 : $setData['become'];
        $setData['become_check'] = empty($setData['become_check']) ? 0 : $setData['become_check'];
        $setData['become_order'] = empty($setData['become_order']) ? 0 : $setData['become_order'];
        $setData['become_child'] = empty($setData['become_child']) ? 0 : $setData['become_child'];
        $setData['become_ordercount'] = empty($setData['become_ordercount']) ? 0 : $setData['become_ordercount'];
        $setData['become_moneycount'] = empty($setData['become_moneycount']) ? 0.00 : $setData['become_moneycount'];
        $setData['become_info'] = empty($setData['become_info']) ? 1 : $setData['become_info'];
        $setData['share_page'] = empty($setData['share_page']) ? 1 : $setData['share_page'];
        $setData['share_page_deail'] = empty($setData['share_page_deail']) ? 0 : $setData['share_page_deail'];
        $setData['reward_points'] = empty($setData['reward_points']) ? 0 : $setData['reward_points'];
        $setData['maximum_number'] = empty($setData['maximum_number']) ? 0 : $setData['maximum_number'];
        return $setData;
    }
}
