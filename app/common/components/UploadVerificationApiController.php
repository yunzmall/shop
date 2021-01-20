<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2020/09/24
 * Time: 上午10:49
 */

namespace app\common\components;


use Yunshop\UploadVerification\service\UploadVerificateRoute;

class UploadVerificationApiController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        if(app('plugins')->isEnabled('upload-verification')){//对接百度内容审核

            $uploadVerification = UploadVerificateRoute::getApiParam();
            $route = \YunShop::request()->route;

            if (in_array($route, array_keys($uploadVerification)) ) {
                $this->TextVerificate($uploadVerification[$route]);
            }

        }
    }

    public function TextVerificate($data)
    {
        $uploadData =  request()->only($data);
        $result = [];

        array_walk_recursive($uploadData, function($value) use (&$result) {
            array_push($result, $value);
        });
        $result = implode(',',array_values($result));
        \Log::info('内容审核文字内容', $result);
        $result = do_upload_verificaton($result, 'text');

        \Log::info('内容审核文字结果', $result);
        if($result[0]['status'] == 0){
            exit( $this->errorJson($result[0]['msg']));
        }

    }
}