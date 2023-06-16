<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/21
 * Time: 17:32
 */

namespace app\outside\controllers;

use app\common\exceptions\ApiException;
use app\common\models\Address;
use app\common\models\Street;


class AddressController extends OutsideController
{
    public function getAddress()
    {
        $addressData = [];

        $type = request()->input('level');

        $parent_id = request()->input('parent_id');

        switch ($type) {
            case 'province':

                $addressData = Address::getProvince();
                break;
            case 'city':
                $addressData = Address::getCityByParentId($parent_id);
                break;
            case 'district':
                $addressData = Address::getAreaByParentId($parent_id);
                break;
            case 'street':
                if (!\Setting::get('shop.trade.is_street')) {
                    throw new ApiException('商城未开启街道级地址设置');
                }
                $addressData = Street::getStreetByParentId($parent_id);
                break;
        }

        return $this->successJson('list', $addressData);
    }
}