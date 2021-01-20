<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2020/9/7
 * Time: 17:33
 */

namespace app\backend\modules\uploadVerificate;


use app\common\components\BaseController;
use app\framework\Http\Request;
use Yunshop\UploadVerification\service\UploadVerificateRoute;

class UploadVerificationBaseController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        if(app('plugins')->isEnabled('upload-verification')  && request()->isMethod('post')){//对接百度内容审核

            $uploadVerification = UploadVerificateRoute::getBackendParam();
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
            if(request()->ajax()){
                exit( $this->errorJson($result[0]['msg']));
            }else{
                exit( $this->message($result[0]['msg'],'','error'));
            }
        }

    }

}
