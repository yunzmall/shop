<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/2/15
 * Time: 16:44
 */

namespace app\backend\modules\finance\controllers;

use app\backend\modules\balance\services\BalanceRechargeService;
use app\backend\modules\upload\services\FileService;
use app\common\components\BaseController;
use app\common\models\finance\BalanceRechargeCheck;
use Illuminate\Support\Facades\Storage;

class BalanceRechargeCheckController extends BaseController
{
    public function index()
    {
        return view('finance.balance.check-list',['is_can_check' => can('balanceRechargeCheckUpdate') ? 1 : 0]);
    }

    public function getList()
    {
        $search = request()->search;
        $pageSize = 20;
        $list = BalanceRechargeCheck::uniacid()->search($search)
            ->with([
                'member' => function ($member) {
                    $member->select('uid','nickname','avatar','nickname','realname','mobile');
                },
                'adminUser' => function ($adminUser) {
                    $adminUser->select('uid','username');
                },
            ])
            ->orderBy('id','desc')
            ->paginate($pageSize);
        return $this->successJson('ok',$list);
    }

    /**
     * 文件转数据流
     * @return void
     */
    public function downloadFile()
    {
        @ini_set('memory_limit', -1);
        $url = request()->url;
        $temp = file_get_contents($url);
        if ($temp === false){
            http_response_code(404);
            die();
        }
        $name = basename($url);
        ob_clean();
        header('Content-Type:application/octet-stream');
        header('Content-Disposition:attachment; filename=' . $name);
        echo $temp;
        die();
    }

    public function check()
    {
        try {
            $service = new BalanceRechargeService();
            $service->verifyChargeLog(request()->id,request()->status);
            return $this->successJson('审核成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function uploadFile()
    {
        try {
            $service = new FileService();
            $service->setUploadPath('balance_enclosure/'.\YunShop::app()->uniacid);
            $file = $service->upload();
            return $this->successJson('上传附件成功',[
                'file' => $file,
                'file_src' => yz_tomedia($file)
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}
