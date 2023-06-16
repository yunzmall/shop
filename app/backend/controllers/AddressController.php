<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/27
 * Time: 下午4:26
 */

namespace app\backend\controllers;

use app\common\components\BaseController;
use app\common\models\Address;
use app\common\models\Street;

class AddressController extends BaseController
{
    public function getAddress()
    {
        $addressData = [];
        switch (\YunShop::request()->type) {
            case 'province':

                $addressData = Address::getProvince();
                break;
            case 'city':
                $addressData = Address::getCityByParentId(\YunShop::request()->parentid);
                break;
            case 'district':
                $addressData = Address::getAreaByParentId(\YunShop::request()->parentid);
                break;
            case 'street':
                $addressData = Street::getStreetByParentId(\YunShop::request()->parentid);
                break;
        }

        echo json_encode($addressData);
    }

    public function getAjaxAddress()
    {
        $addressData = [];
        switch (request()->input('type')) {
            case 'province':
                $addressData = Address::getProvince();
                break;
            case 'city':
                $addressData = Address::getCityByParentId(request()->input('parentid'));
                break;
            case 'district':
                $addressData = Address::getAreaByParentId(request()->input('parentid'));
                break;
            case 'street':
                $addressData = Street::getStreetByParentId(request()->input('parentid'));
                break;
        }

        return $this->successJson('ok',$addressData);
    }

    public function getAjaxExpress()
    {
        $data = \app\common\repositories\ExpressCompany::create()->all();

        return $this->successJson('ok',$data);
    }

    public function test()
    {
        $pay = new \app\common\services\AliPay();

        $result = $pay->withdrawCert('ywkpgl2852@sandbox.com','沙箱环境','CS'.time(),'1');

        dd($result);
    }

}