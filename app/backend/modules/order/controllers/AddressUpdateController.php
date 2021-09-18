<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/27
 * Time: 15:24
 */

namespace app\backend\modules\order\controllers;


use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\models\Address;
use app\common\models\order\Address as OrderAddress;
use app\common\models\order\AddressUpdateLog;
use app\common\models\Street;

class AddressUpdateController extends BaseController
{
    public function index()
    {
        $order_id = intval(request()->input('order_id'));

        $list = AddressUpdateLog::uniacid()->where('order_id', $order_id)->get();


        return $this->successJson('list', $list);
    }

    public function update()
    {
        $data = request()->input('data');

        $orderAddress =  OrderAddress::where('order_id', $data['order_id'])->first();

        $old_address = $orderAddress->address;

        $new_address = $this->getAddressName($data);

        $createData = [
            'uniacid' => \YunShop::app()->uniacid,
            'user_id' => intval(\YunShop::app()->uid),
            'order_id' => $data['order_id'],
            'province_id' => $data['province_id'],
            'city_id' => $data['city_id'],
            'district_id' => $data['district_id'],
            'street_id' => intval($data['street_id']),
            'realname'    => $data['realname'],
            'phone'    => $data['phone'],
            'old_phone'    => $orderAddress->mobile,
            'old_name'    => $orderAddress->realname,
            'old_address' => $old_address,
            'new_address' => $new_address,
        ];

        $updateData = [
            'realname'    => $data['realname'],
            'mobile'    => $data['phone'],
            'address' => $new_address,
        ];

        $orderAddress->fill($updateData);
        $bool =  $orderAddress->save();

        if ($bool) {
            //保存修改记录
            $addressUpdate = new AddressUpdateLog();
            $addressUpdate->fill($createData);
            $bool2 =  $addressUpdate->save();

            return $this->successJson('修改成功');
        }

        return $this->errorJson('修改失败');

    }

    public function getAddressName($data)
    {
        $address['province_name'] = Address::where('id',$data['province_id'])->value('areaname');
        $address['city_name'] = Address::where('id',$data['city_id'])->value('areaname');
        $address['district_name'] = Address::where('id',$data['district_id'])->value('areaname');
        if ($data['street_id']) {
            $address['street_name'] = Street::where('id', $data['street_id'])->value('areaname');
        }

        $address['address'] = $data['address'];


        return implode(' ', $address);
    }
}