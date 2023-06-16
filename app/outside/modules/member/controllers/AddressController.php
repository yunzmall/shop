<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/21
 * Time: 10:40
 */

namespace app\outside\modules\member\controllers;


use app\frontend\repositories\MemberAddressRepository;
use app\outside\controllers\OutsideController;

class AddressController extends OutsideController
{
    public function index()
    {
        $this->validate([
            'uid' => 'required|integer',
        ],null, [
            'title.required' => '会员标识不能为空',
            'contents.integer' =>  '会员标识必须是纯数字',
        ]);

        $uid = request()->input('uid');

        $memberAddress = app(MemberAddressRepository::class)->getAddressList($uid);


        return $this->successJson('list', $memberAddress);
    }
}