<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/11
 * Time: 上午11:44
 */

namespace app\backend\modules\finance\controllers;


use app\backend\modules\finance\services\PointService;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\backend\modules\member\models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Yunshop\Assemble\Common\Models\OrderBonusModel;

class PointMemberController extends BaseController
{
    /**
     * @var LengthAwarePaginator
     */
    private $recordsModels;

    private $shopSet;

    public function preAction()
    {
        parent::preAction();
        $this->recordsModels = $this->recordsModels()->paginate()->toArray();
        $this->shopSet = Setting::get('shop.member');
        $this->shopSet['level_name'] = $this->shopSet['level_name'] ?: '普通会员';
        $this->shopSet['group_name'] = '无分组';
        foreach ($this->recordsModels['data'] as &$item) {
            $item['member']['avatar'] =  $item['member']['avatar'] ? tomedia($item['member']['avatar'] ) : tomedia($this->shopSet['headimg']);
            $item['member']['nickname'] = $item['member']['nickname'] ?: '未更新';
        }
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->successJson('ok', $this->resultData());
        }
        return view('finance.point.point_member');
    }

    private function resultData()
    {
        $data = [
            'search' => $this->searchParams(),
            'memberList' => $this->recordsModels,
            'amount' => $this->recordsModels()->sum('credit1'),
            'transfer_love' => $this->isShow(),
            'memberGroup' => MemberGroup::getMemberGroupList(),
            'memberLevel' => MemberLevel::getMemberLevelList(),
            'tab_list' => PointService::getVueTags(),
            'shopSet' => $this->shopSet
        ];
        return $data;
    }

    /**
     * 页面分页html
     *
     * @return string
     */
    private function page()
    {
        return PaginationHelper::show($this->recordsModels->total(), $this->recordsModels->currentPage(), $this->recordsModels->perPage());
    }

    /**
     * @return LengthAwarePaginator
     */
    private function recordsModels()
    {
        if ($search = \YunShop::request()) {
            return Member::searchMembers($search, 'credit1');
        }
        return Member::getMembers()->withoutDeleted();
    }

    /**
     * 搜索参数
     *
     * @return array
     */
    public function searchParams()
    {
        return request()->search ?: [];
    }

    /**
     * 是否显示转入爱心值设置
     *
     * @return bool
     */
    private function isShow()
    {
        return $this->lovePlugin() && $this->transferSet();
    }

    /**
     * 积分是否开启转入爱心值
     *
     * @return bool
     */
    private function transferSet()
    {
        return !!Setting::get('point.set.transfer_love');
    }

    /**
     * 爱心值插件是否开启
     *
     * @return bool
     */
    private function lovePlugin()
    {
        return app('plugins')->isEnabled('love');
    }
}
