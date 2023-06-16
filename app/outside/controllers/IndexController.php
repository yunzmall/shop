<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/5
 * Time: 15:10
 */

namespace app\outside\controllers;


use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\models\AccountWechats;
use app\outside\services\ClientService;
use Illuminate\Support\Facades\DB;

class IndexController extends OutsideController
{
    public function index()
    {

        dd('测试');

        $client = new ClientService();
        $client->setRoute('index.index');
        $client->setData('sign_type', 'SHA256');
        $client->setData('uid', 2455);
        $client->setData('order_sn', 'RW3346346346');
        $client->setData('desc', '测试接口');
        $result = $client->post();
        dd($result);

        dd('测试');
    }

}