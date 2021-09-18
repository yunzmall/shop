<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/9/4
 * Time: 下午5:00
 */

namespace app\frontend\modules\dispatch\models;


use app\common\models\Address;
use app\common\models\DispatchType;
use app\frontend\models\OrderAddress;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\repositories\MemberAddressRepository;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\models\Street;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PreOrderAddress extends OrderAddress
{

    /**
     * @var PreOrder
     */
    public $order;
    private $memberAddress;

    /**
     * @param PreOrder $order
     * @throws ShopException
     */
    public function setOrder(PreOrder $order)
    {
        $this->order = $order;

        $order->setRelation('orderAddress', $this);
        $this->_init();
    }


    /**
     * @throws ShopException
     */
    protected function _init()
    {
        $this->fill($this->getOrderAddress()->toArray());
    }

    public function validateAddress()
    {
        if (!$this->order->isNeedAddress()) {

            if (empty(trim($this->address))) {
                throw new AppException("收货地址不能为空");
            }

            if($this->isRegion()) {
                $address = $this->getMemberAddress();
                if (empty($this->province_id)) {
                    throw new AppException("收货地址有误,省份[{$address->province}]不存在");
                }
                if (empty($this->city_id)) {
                    throw new AppException("收货地址有误,城市[{$address->city}]不存在");
                }
                if (empty($this->district_id)) {
                    throw new AppException("收货地址有误,区县[{$address->district}]不存在");
                }
            }
        }
    }

    /**
     * @return OrderAddress
     * @throws ShopException
     */
    protected function getOrderAddress()
    {
        if (!isset($this->memberAddress)) {
            // $this->memberAddress = $this->_getMemberAddress();
            $this->memberAddress = $this->isRegion() ? $this->_getMemberAddress() : $this->_getAddress();
        }
        return $this->memberAddress;
    }

    /**
     * @return bool true 需要；false 不
     */
    protected function isRegion()
    {
        $is_region = \Setting::get('shop.trade.is_region');

        return !$is_region;
    }


    /**
     * 不需要区域的下单地址
     */
    private function _getAddress()
    {
        $member_address = $this->getMemberAddress();

        $orderAddress = new OrderAddress();

        $orderAddress->order_id = $this->order->id;

        $orderAddress->mobile = $member_address->mobile;

        $orderAddress->realname = $member_address->username;

        $province_id = $member_address->province_id ?: Address::where('areaname', $member_address->province)->where('level',1)->value('id');
        $city_id = $member_address->city_id ?: Address::where('areaname', $member_address->city)->where('parentid', $province_id)->value('id');
        $district_id = $member_address->district_id ?: Address::where('areaname', $member_address->district)->where('parentid', $city_id)->value('id');


        $orderAddress->province_id = $province_id?:0;
        $orderAddress->city_id = $city_id?:0;
        $orderAddress->district_id = $district_id?:0;

        if (isset($member_address->street)) {
            $orderAddress->street_id = Street::where('areaname', $member_address->street)->where('parentid', $orderAddress->district_id)->value('id') ?:0;
        }

        $orderAddress->province = $member_address->province ?:'';
        $orderAddress->city = $member_address->city ?:'';
        $orderAddress->district = $member_address->district ?:'';
        $orderAddress->street =  $member_address->street ?:'';

        $orderAddress->address = implode(' ', array_filter([$member_address->province, $member_address->city, $member_address->district, $orderAddress->street, $member_address->address]));

        return $orderAddress;
    }

    /**
     * 获取用户配送地址模型
     * @return mixed
     * @throws AppException
     */
    private function _getMemberAddress()
    {
        $member_address = $this->getMemberAddress();

        $orderAddress = new OrderAddress();

        $orderAddress->order_id = $this->order->id;

        $orderAddress->mobile = $member_address->mobile;
        $orderAddress->province_id = $member_address->province_id ?: Address::where('areaname', $member_address->province)->where('level',1)->value('id');

        $orderAddress->city_id = $member_address->city_id ?: Address::where('areaname', $member_address->city)->where('parentid', $orderAddress->province_id)->value('id');

        $orderAddress->district_id = $member_address->district_id ?: Address::where('areaname', $member_address->district)->where('parentid', $orderAddress->city_id)->value('id');
        $orderAddress->address = implode(' ', [$member_address->province, $member_address->city, $member_address->district, $member_address->address]);

        //todo 修复前端传street等于空字符串时会报错
        if (!empty($member_address->street) && $member_address->street != '其他') {
            $orderAddress->street_id = Street::where('areaname', $member_address->street)->where('parentid', $orderAddress->district_id)->value('id');
            if (!isset($orderAddress->street_id)) {
                throw new AppException('收货地址有误请重新保存收货地址');
            }
            $orderAddress->street = $member_address->street;
            $orderAddress->address = implode(' ', [$member_address->province, $member_address->city, $member_address->district, $orderAddress->street, $member_address->address]);

        }

//        if (isset($member_address->street) && $member_address->street != '其他') {
//            $orderAddress->street_id = Street::where('areaname', $member_address->street)->where('parentid', $orderAddress->district_id)->value('id');
//            if (!isset($orderAddress->street_id)) {
//                throw new AppException('收货地址有误请重新保存收货地址');
//            }
//            $orderAddress->street = $member_address->street;
//            $orderAddress->address = implode(' ', [$member_address->province, $member_address->city, $member_address->district, $orderAddress->street, $member_address->address]);
//
//        } elseif (isset($member_address->street) && $member_address->street != '其他') {
//            $orderAddress->street = $member_address->street;
//            $orderAddress->address = implode(' ', [$member_address->province, $member_address->city, $member_address->district, $orderAddress->street, $member_address->address]);
//        }

        $orderAddress->realname = $member_address->username;
        $orderAddress->province = $member_address->province;
        $orderAddress->city = $member_address->city;
        $orderAddress->district = $member_address->district;
        // $orderAddress->zipcode = $member_address->zipcode;

        return $orderAddress;
    }

    /**
     * 获取用户配送地址模型
     * @return mixed
     * @throws AppException
     */
    public function getMemberAddress()
    {
        $address = json_decode(urldecode($this->order->getRequest()->input('address', '[]')), true);

        if (count($address)) {
            //$request->input('address');
            $this->validate($address, [
                    'address' => 'required',
                    'mobile' => 'required',
                    'username' => 'required',
                    //province' => 'required',
                    //'city' => 'required',
                    // 'zipcode' => '',
                    //'district' => 'required'
                ]
            );
            $memberAddress = app(MemberAddressRepository::class)->fill($address);

            return $memberAddress;
        }

        $defaultAddress = $this->order->belongsToMember->defaultAddress;
//        if (empty($defaultAddress)) {
//            $defaultAddress = app(MemberAddressRepository::class)->getStaticModel()->where('uid', $this->order->uid)->first();
//        }

        return $defaultAddress;
    }
    public function beforeSaving()
    {
        return parent::beforeSaving(); // TODO: Change the autogenerated stub
    }

    public function save(array $options = [])
    {
        if ($this->order->isNeedAddress()) {
            \Log::info('---订单编号:'.$this->order->order_sn.'不需要地址----------');
            return true;
        }
        return parent::save($options); // TODO: Change the autogenerated stub
    }
}