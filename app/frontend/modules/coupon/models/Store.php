<?php

namespace app\frontend\modules\coupon\models;

class Store extends \app\common\models\Store
{


    public function city()
    {
        return $this->hasOne('app\common\models\Address', 'id', 'city_id')->select(['id', 'areaname']);
    }

    public function district()
    {
        return $this->hasOne('app\common\models\Address', 'id', 'district_id')->select(['id', 'areaname']);
    }

    /**
     * 获取门店
     * @param array $storeIds
     * @param $lat
     * @param $lng
     * @return mixed
     */
    public function getStoreList (array $storeIds = [], $lat, $lng)
    {
        $store_mo = $this->uniacid();
        $store_mo->selectRaw('store_name, city_id, district_id, street_id, address, thumb, id');

        $store_mo->when(($lat && $lng), function ($query) use ($lat, $lng) {
            $query->selectRaw(
                "(round(6367000 * 2 * asin(sqrt(pow(sin(((latitude * pi()) / 180 - ({$lat} * pi()) / 180) / 2), 2) + cos(({$lat} * pi()) / 180) * cos((latitude * pi()) / 180) * pow(sin(((longitude * pi()) / 180 - ({$lng} * pi()) / 180) / 2), 2))))) AS distance"
            )->orderBy('distance', 'asc');
        });
        if($storeIds == null){ //全门店 只展示10条
            $store_list = $store_mo->limit(10)->get();
        }else{
            $store_mo = $store_mo->whereIn('id', $storeIds);
            $store_list = $store_mo->paginate(16,[],'store_page');
        }

        foreach ($store_list as &$store) {
            $store->thumb = yz_tomedia($store->thumb);
            $store->address = $store->city->areaname.$store->district->areaname.$store->street->areaname.$store->address;
            if($lat && $lng){
                $store->unit = 'm';
                if ($store->distance >= 1000) {
                    $store->distance = round($store->distance / 1000, 1);
                    $store->unit = 'km';
                }
            }
        }

        return $store_list;
    }
}