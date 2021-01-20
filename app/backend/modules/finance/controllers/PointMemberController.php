<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/11
 * Time: 上午11:44
 */

namespace app\backend\modules\finance\controllers;


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

    public function preAction()
    {
        parent::preAction();
        $this->recordsModels = $this->recordsModels();
    }

    public function index()
    {
        return view('finance.point.point_member', $this->resultData());
    }

    private function resultData()
    {
        return [
            'search'        => $this->searchParams(),
            'memberList'    => $this->recordsModels,
            'page'         => $this->page(),
            'amount'        => Member::uniacid()->withoutDeleted()->sum('credit1'),
            'transfer_love' => $this->isShow(),
            'memberGroup'   => MemberGroup::getMemberGroupList(),
            'memberLevel'   => MemberLevel::getMemberLevelList()
        ];
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
            return Member::searchMembers($search, 'credit1')->paginate();
        }
        return Member::getMembers()->withoutDeleted()->paginate();
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
