<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/27
 * Time: 上午10:44
 */

namespace app\backend\modules\member\controllers;


use app\backend\modules\goods\models\Goods;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\member\models\MemberShopInfo;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;

class MemberLevelController extends BaseController
{
    /**
     * 加载模板
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        return view('member.level.list', [])->render();
    }

    /*
     * Member level pager list
     * 17.3,31 restructure
     *
     * @autor yitian */
    public function show()
    {
        $pageSize = 10;
        $levelList = MemberLevel::getLevelPageList($pageSize);
        return $this->successJson('ok', [
            'levelList' => $levelList,
            'level_type' => Setting::get('shop.member')['level_type']
        ]);

    }

    public function searchGoods()
    {
        $goods = Goods::getGoodsByNameLevelNew(request()->keyword);
        foreach ($goods as $k => $v) {
            $goods[$k]['thumb'] = yz_tomedia($v['thumb']);
        }
        return $this->successJson('ok', [
            'goods' => $goods,
        ]);
    }

    /*
     * Add member level
     *
     * @autor yitian */
    public function form()
    {
        $id = request()->id;
        return view('member.level.form', [
            'id' => $id,
            'shopSet' => Setting::get('shop.member'),
            'integral' => (app('plugins')->isEnabled('integral') && \Yunshop\Integral\Common\Services\SetService::getIntegralSet()['member_show']) ? 1 : 0
        ])->render();
    }

    public function store()
    {
        $levelModel = new memberLevel();
        $requestLevel = \YunShop::request()->level;
        $get_setting = Setting::get('shop.member');
        $shop_set = [
            'level_type' => $get_setting['level_type'],
            'level_discount_calculation' => empty($get_setting['level_discount_calculation']) ? 0 : $get_setting['level_discount_calculation']
        ];
        if ($requestLevel) {
            //将数据赋值到model
            $levelModel->fill($requestLevel);
            //其他字段赋值
            $levelModel->uniacid = \YunShop::app()->uniacid;
            unset($levelModel->goods);
            unset($levelModel->goods_id);

            if ($requestLevel['goods']) {
                foreach ($requestLevel['goods'] as $k => $v) {
                    if ($v['goods_id']) {
                        $arr[] = $v['goods_id'];
                    }
                }
            } else {
                $arr[] = [];
            }

            if (empty($requestLevel['goods_id'])) {
                $levelModel->goods_id = implode(',', array_unique($arr));
            } else {
                $ids = implode(',', array_unique(array_merge(array_filter($arr), array_values($requestLevel['goods_id']))));
                $levelModel->goods_id = $ids;
            }
            //字段检测
            $validator = $levelModel->validator();
            if ($validator->fails()) {//检测失败
                return $this->errorJson($validator->errors()->first(),$shop_set);
            } else {
                //数据保存
                if ($levelModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('添加会员等级成功', ['data' => true]);
                } else {
                    return $this->errorJson('添加会员等级失败');
                }
            }
        }

        return $this->successJson('ok', [
            'shopSet' => $shop_set
        ]);
    }

    /**
     * Modify membership level
     */
    public function update()
    {
        $levelModel = MemberLevel::getMemberLevelById(\YunShop::request()->id);
        if (!$levelModel) {
            return $this->message('无此记录或已被删除', '', 'error');
        }
        $requestLevel = \YunShop::request()->level;
        $requestLevel['order_count'] = isset($requestLevel['order_count']) ? $requestLevel['order_count'] : 0;
        if ($levelModel['goods_id']) {
            $goods = MemberLevel::getGoodsId($levelModel['goods_id']);
            $levelModel->goods = $goods ? $goods->toArray() : [];
        }
        $get_setting = Setting::get('shop.member');
        $shop_set = [
            'level_type' => $get_setting['level_type'],
            'level_discount_calculation' => empty($get_setting['level_discount_calculation']) ? 0 : $get_setting['level_discount_calculation']
        ];
        if ($requestLevel) {
            if (!isset($requestLevel['goods_id'])) {
                $requestLevel['goods_id'] = 0;
            }else{
                $requestLevel['goods_id'] = implode(',',array_unique(array_filter($requestLevel['goods_id'])));
            }
            unset($levelModel->goods);
            unset($requestLevel['goods']);
            $levelModel->fill($requestLevel);

            $validator = $levelModel->validator();
            if ($validator->fails()) {//检测失败
                return $this->errorJson($validator->messages());
            } else {
//                $saveModel = \app\common\models\MemberLevel::find(\YunShop::request()->id);
//                $bool = $saveModel->fill($requestLevel)->save();
                if ($levelModel->fill($requestLevel)->save()) {
                    return $this->successJson('修改会员等级信息成功', ['data' => true]);
                } else {
                    return $this->errorJson('修改会员等级信息失败');
                }
            }
        }

        return $this->successJson('获取数据成功', [
            'levelModel' => $levelModel,
            'shopSet' => $shop_set
        ]);
    }

    /*
     * Delete membership
     *
     * @author yitain */
    public function destroy()
    {
        $id = \YunShop::request()->id;
        $uniacid = \YunShop::app()->uniacid;
        $levelModel = MemberLevel::getMemberLevelById($id);
        if (!$levelModel) {
            return $this->error('未找到记录或已删除');
        }
        if ($levelModel->where(['uniacid' => $uniacid, 'id' => $id])->delete()) {
            MemberShopInfo::where('level_id', $id)->update(['level_id' => '0']);
            return $this->successJson('删除等级成功', ['data' => true]);
        } else {
            return $this->error('删除等级失败');
        }
    }


    public function getLevel()
    {
        $keyword = request()->keyword;

        $level = MemberLevel::uniacid()->where('level_name', 'like', '%' . $keyword . '%')->select('id', 'level_name')->get()->toArray();
        return $this->successJson('ok', $level);
    }

}