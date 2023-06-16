<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/11/24
 * Time: 12:26
 */
namespace app\frontend\modules\postageIncludedCategory\controllers;

use app\common\facades\Setting;
use app\frontend\modules\cart\controllers\ListController;


class CartListController extends ListController
{
    public function index()
    {
        $content = parent::index();
        $data = $content->getData();
        if ($data->result && Setting::get('enoughReduce.freeFreight.open')) {
            $enough = Setting::get('enoughReduce.freeFreight.enough') ?: 0;
            $data->data->postage_included_msg = '';
            if ($data->data->total_amount < $enough) {
                $difference = bcsub($enough, $data->data->total_amount, 2);
                $data->data->postage_included_msg = $difference;
            }
            $content->setData($data);

        } else {
            $data->result = 0;
            $data->msg = '请确认订单满额包邮是否开启.';
            $data->data = '';
            $content->setData($data);
        }

        return $content;
    }
}