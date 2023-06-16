<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/25/20 3:25 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:
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
    private $amount;

    public function index()
    {
        return view('balance.member');
    }

    public function resultData()
    {
        $recordsModels = $this->recordsModels();
        $recordsModels->map(function ($item){
            $item->yz_member = $item->yzMember;
            $item->nickname = $item->nickname ?:
                ($item->mobile ? substr($item->mobile, 0, 2) . '******' . substr($item->mobile, -2, 2) : '无昵称会员');
            $item->yz_member->level = $item->yzMember->level;
            $item->yz_member->group = $item->yzMember->group;
        });
        return $this->successJson('ok',[
            'shopSet'       => Setting::get('shop.member'),
            'amount'        => $this->amount,
            'page'          => $this->page($recordsModels),
            'search'        => $this->searchParams(),
            'pageList'      => $recordsModels,
            'memberLevel'   => $this->memberLevels(),
            'memberGroup'   => $this->memberGroups(),
        ]);
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
        $this->amount = $recordsModels->sum('credit2');
        return $recordsModels->orderBy('uid', 'desc')->withoutDeleted()->paginate();
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
