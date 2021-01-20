<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/7/30
 * Time: 上午10:42
 */

namespace app\common\repositories;

use Illuminate\Database\Eloquent\Collection;
use Yunshop\ExpressCompany\Common\Models\ExpressCompanyModel;

class ExpressCompany extends Collection
{
    static public function create()
    {
        $file = implode([app()->path(), '..', 'static', 'source', 'expresscom.json'], DIRECTORY_SEPARATOR);
        $json = file_get_contents($file);

        $items = json_decode($json, true);

        $items = array_filter($items,function ($v) {
            return $v['name'];
        });

        if (app('plugins')->isEnabled('express-company')) {
            $data = ExpressCompanyModel::uniacid()->select('name','value')->get()->toArray();
            $items = array_merge($data,$items);
        }
        return new static($items);
    }
}