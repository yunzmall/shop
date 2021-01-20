<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/24/20 4:32 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\backend\modules\point\controllers;


use app\backend\modules\finance\models\PointLog;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecordsController extends BaseController
{
    public function index()
    {
        return view('point.records', $this->resultData());
    }

    private function resultData()
    {
        $recordsModels = $this->recordsModels();

        return [
            'page'          => $this->page($recordsModels),
            'search'        => $this->searchParams(),
            'pageList'      => $recordsModels,
            'memberLevel'   => $this->memberLevels(),
            'memberGroup'   => $this->memberGroups(),
            'sourceComment' => $this->sourceComment(),
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

