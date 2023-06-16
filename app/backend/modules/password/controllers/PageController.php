<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    5/19/21 4:53 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * 
 * 
 *
 ****************************************************************/


namespace app\backend\modules\password\controllers;


use app\backend\modules\member\models\Member;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;

class PageController extends BaseController
{
    public function index()
    {
        return view('password.update', $this->viewData());
    }

    private function viewData()
    {
        return ['member' => $this->memberModel()];
    }

    private function memberModel()
    {
        if (!$memberModel = $this->_memberModel()) throw new ShopException('会员信息错误');

        return $memberModel->toArray();
    }

    private function _memberModel()
    {
        return Member::select('uid', 'avatar', 'nickname', 'realname', 'mobile')->find($this->memberId());
    }

    private function memberId()
    {
        if (!$member_id = request()->member_id) throw new ShopException('请输入正确的参数');

        return $member_id;
    }
}
