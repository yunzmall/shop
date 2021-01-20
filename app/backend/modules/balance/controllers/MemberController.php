<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/25/20 3:25 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\backend\modules\balance\controllers;


use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberController extends BaseController
{
    public function index()
    {
        return view('balance.member', $this->resultData());
    }

    private function resultData()
    {
        $recordsModels = $this->recordsModels();

        return [
            'shopSet'       => Setting::get('shop.member'),
            'amount'        => $this->amount(),
            'page'          => $this->page($recordsModels),
            'search'        => $this->searchParams(),
            'pageList'      => $recordsModels,
            'memberLevel'   => $this->memberLevels(),
            'memberGroup'   => $this->memberGroups(),
        ];
    }

    /**
     * @return array
     */
    public function searchParams()
    {
        return request()->search ?: [];
    }

    /**
     * @param LengthAwarePaginator $recordsModels
     *
     * @return string
     */
    private function page(LengthAwarePaginator $recordsModels)
    {
        return PaginationHelper::show($recordsModels->total(), $recordsModels->currentPage(), $recordsModels->perPage());
    }

    /**
     * @return LengthAwarePaginator
     */
    private function recordsModels()
    {
        $recordsModels = Member::uniacid();

        if ($search = $this->searchParams()) {
            $recordsModels = $recordsModels->search($search);
        }
        return $recordsModels->orderBy('uid', 'desc')->withoutDeleted()->paginate();
    }

    private function amount()
    {
        return Member::uniacid()->withoutDeleted()->sum('credit2');
    }

    private function memberLevels()
    {
        return MemberLevel::getMemberLevelList();
    }

    private function memberGroups()
    {
        return MemberGroup::getMemberGroupList();
    }
}
