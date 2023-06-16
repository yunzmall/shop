<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/24/20 4:32 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:     
 ****************************************************************/


namespace app\backend\modules\point\controllers;


use app\backend\modules\finance\models\PointLog;
use app\backend\modules\finance\services\PointService;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecordsController extends BaseController
{
    public function index()
    {
        if (request()->ajax()) {
            return $this->successJson('ok', $this->resultData());
        }
        return view('point.records');
    }

    private function resultData()
    {
        $recordsModels = $this->recordsModels()->toArray();
        $shopSet = Setting::get('shop.member');

        foreach ($recordsModels['data'] as &$item) {
            $item['member']['avatar'] = $item['member']['avatar'] ? tomedia($item['member']['avatar']) : tomedia($shopSet['headimg']);
            $item['member']['nickname'] = $item['member']['nickname'] ?: '未更新';
            $item['member']['uid'] = $item['member']['uid'] ?: '';
        }
        return [
            'search' => $this->searchParams(),
            'pageList' => $recordsModels,
            'memberLevel' => $this->memberLevels(),
            'memberGroup' => $this->memberGroups(),
            'sourceComment' => $this->sourceComment(),
            'tab_list' => PointService::getVueTags(),
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
        $recordsModels = PointLog::uniacid()->with(['member']);
        if (request()->member_id) {
            $recordsModels->where('member_id', request()->member_id);
        }
        if ($search = $this->searchParams()) {
            $recordsModels = $recordsModels->search($search);
        }
        return $recordsModels->orderBy('id', 'desc')->paginate();
    }

    private function memberLevels()
    {
        return MemberLevel::getMemberLevelList();
    }

    private function memberGroups()
    {
        return MemberGroup::getMemberGroupList();
    }

    private function sourceComment()
    {
        return (new PointLog())->sourceComment();
    }
}

