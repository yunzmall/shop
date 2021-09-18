<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 19/03/2017
 * Time: 00:48
 */

namespace app\backend\controllers;


use app\backend\modules\charts\models\Supplier;
use app\backend\modules\survey\controllers\SurveyController;
use app\common\components\BaseController;
use app\common\models\user\WeiQingUsers;
use app\common\services\CollectHostService;
use app\common\services\PermissionService;
use Illuminate\Support\Facades\DB;
use Yunshop\Merchant\common\models\Merchant;
use Yunshop\StoreCashier\store\admin\StoreIndexController;
use Yunshop\Supplier\supplier\controllers\SupplierIndexController;
use Yunshop\StoreCashier\common\models\Store;

class IndexController extends BaseController
{
    protected $isPublic = true;

    public function index()
    {
        $uid = \YunShop::app()->uid;
        $user = WeiQingUsers::getUserByUid($uid)->first();

        if(PermissionService::isFounder() or PermissionService::isOwner() or PermissionService::isManager()){
            return redirect(yzWebFullUrl('survey.survey.index'));
        }

        if (app('plugins')->isEnabled('store-cashier')) {
            $store = Store::getStoreByUserUid($uid)->first();

            if ($store && $user) {
                return StoreIndexController::index();
            }
        }

        if (app('plugins')->isEnabled('supplier')) {
            $supplier = Supplier::getSupplierByUid($uid)->first();

            if ($supplier && $user) {
                return SupplierIndexController::index();
            }
        }

        if (app('plugins')->isEnabled('merchant')) {
            $merchant = Merchant::select()->where('user_uid', $uid)->first();
            if ($merchant) {
                if ($merchant->is_center == 1) {
                    return \Yunshop\Merchant\merchant\admin\IndexController::center();
                } else {
                    return \Yunshop\Merchant\merchant\admin\IndexController::staff();
                }
            }
        }
        if (app('plugins')->isEnabled('work-wechat')) {
            if($user['type']==3){
                //是企业微信管理员，跳转到企业微信管理首页
                $crop_info = \Yunshop\WorkWechatPlatform\common\models\Crop::getByUid($uid);
                $crop_id = $crop_info->id;
                if($crop_id){
                    //当前用户是企业微信总的管理员
                    \Yunshop\WorkWechatPlatform\common\utils\CropUtils::setCropId($crop_id);
                    \Yunshop\WorkWechatPlatform\common\utils\CropUtils::setCropName($crop_info->name);
                    return redirect(\Yunshop\WorkWechat\common\utils\Url::absoluteManageIndexUrl());
                }else{
                    $work_wechat_user = \Yunshop\WorkWechat\common\models\WorkWechatUser::getOneByUid($uid);
                    if($work_wechat_user->id){
                        //当前用户是企业微信操作员
                        $crop_id = $work_wechat_user->crop_id;
                        \Yunshop\WorkWechatPlatform\common\utils\CropUtils::setCropId($crop_id);
                        \Yunshop\WorkWechatPlatform\common\utils\CropUtils::setCropName($crop_info->name);
                        return redirect(\Yunshop\WorkWechat\common\utils\Url::absoluteManageIndexUrl());
                    }

                }
            }

        }

        (new CollectHostService(request()->getHttpHost()))->handle();

        $designer = (new \app\backend\controllers\PluginsController)->canAccess('designer');

        if (is_null($designer)) {
            $designer = (new \app\backend\controllers\PluginsController)->canAccess('decorate');
        }

        return view('index',['designer' => $designer])->render();
    }

    public function changeField()
    {
        $sql = 'ALTER TABLE `' . DB::getTablePrefix() . 'mc_members` MODIFY `pay_password` varchar(30) NOT NULL DEFAULT 0';

        try {
            DB::select($sql);
            echo '数据已修复';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function changeAgeField()
    {
        $sql = 'ALTER TABLE `' . DB::getTablePrefix() . 'mc_members` MODIFY `age` tinyint(3) NOT NULL DEFAULT 0';

        try {
            DB::select($sql);
            echo '数据已修复';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}