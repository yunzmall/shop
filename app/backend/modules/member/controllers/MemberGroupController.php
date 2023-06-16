<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/23
 * Time: 下午6:08
 */

namespace app\backend\modules\member\controllers;


use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberShopInfo;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;

class MemberGroupController extends BaseController
{
    /**
     * 加载模板
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        return view('member.group.list', [])->render();
    }
    /*
     * Member group pager list
     * 17.3,31 restructure
     *
     * @autor yitian */
    public function show()
    {
        $pageSize = 20;
        $groupList = MemberGroup::getGroupPageList($pageSize);
        $groupList = is_null($groupList) ? [] : $groupList->toArray();
        foreach ($groupList['data'] as $k => $v) {
            $groupList['data'][$k]['member']['count'] = count($v['member']);
        }
        return $this->successJson('ok', [
            'groupList' => $groupList,
        ]);
    }

    /**
     * 加载试图
     * @return string
     */
    public function form()
    {
        $id = request()->id;
        return view('member.group.form', ['id' => $id])->render();
    }
    /*
     * Add member group
     * 17.3,31 restructure
     *
     * @autor yitian */
    public function store()
    {
        $groupModel = new MemberGroup();

        $requestGroup = \YunShop::request()->group;
        if ($requestGroup) {
            $groupModel->setRawAttributes($requestGroup);
            $groupModel->uniacid = \YunShop::app()->uniacid;

            $validator = $groupModel->validator($groupModel->getAttributes());
            if ($validator->fails()) {
                $this->error($validator->messages());
            } else {
                if ($groupModel->save()) {
                    return $this->successJson('添加会员分组成功', ['data' => true]);
                } else {
                    $this->error("添加会员分组失败");
                }
            }
        }

        return $this->successJson('ok', [
            'groupModel' => $groupModel
        ]);
    }
    /*
     *  Update member group
     * */
    public function update()
    {
        $groupModel = MemberGroup::getMemberGroupByGroupId(\YunShop::request()->group_id);
        if(!$groupModel) {
            return $this->error('未找到会员分组或已删除');
        }
        $group = \YunShop::request()->group;
        if ($group) {
            $requestGroup['group_name'] = $group['group_name'];
            $requestGroup['uniacid'] = \YunShop::app()->uniacid;
            $groupModel->setRawAttributes($requestGroup);
            $validator = $groupModel->validator($requestGroup);
            if ($validator->fails()) {
                $this->error($validator->messages());
            } else {
                if ($groupModel->save()) {
                    return $this->successJson('修改会员分组成功', ['data' => true]);
                } else {
                    $this->error('修改会员分组信息失败！！！');
                }
            }
        }

        return $this->successJson('ok', [
            'groupModel' => $groupModel
        ]);
    }
    /*
     * Destory member group
     *
     * */
    public function destroy()
    {
        $groupModel = MemberGroup::getMemberGroupByGroupId(\YunShop::request()->group_id);
        if (!$groupModel) {
            $this->error('未找到会员分组或已删除');
        }
        if ($groupModel->delete()) {
            MemberShopInfo::where('group_id',\YunShop::request()->id)->update(['group_id'=>'0']);
            return $this->successJson('删除会员分组成功', ['data' => true]);
        } else {
            $this->error("删除会员分组失败");
        }
    }

    public function getGroup()
    {
        $keyword = request()->keyword;

        $level = MemberGroup::uniacid()->where('group_name','like','%'.$keyword.'%')->select('id','group_name')->get()->toArray();
        return $this->successJson('ok',$level);
    }
}
