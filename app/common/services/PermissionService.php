<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 08/03/2017
 * Time: 09:39
 */

namespace app\common\services;


use app\common\exceptions\ShopException;
use app\common\models\Menu;
use app\common\models\user\UniAccountUser;
use app\common\models\user\User;

class PermissionService
{

    public static function validate()
    {
        $item = \app\common\models\Menu::getCurrentItemByRoute(request()->getRoute(), \app\backend\modules\menu\Menu::current()->getItems());
        //检测权限
        if (!PermissionService::can($item)) {
            $exception = new ShopException('Sorry,您没有操作权限，请联系管理员!');
            $exception->setRedirect(yzWebUrl('index.index'));
            throw $exception;
        }
        return true;
    }

    public static function isAuth()
    {
        return \YunShop::app()->uid;
    }

    /**
     * 检测是否有权限
     * @param $item
     * @return bool
     */
    public static function can($item)
    {
        /*if(!$item){
            return false;
        }*/
        if (\Yunshop::isPHPUnit()) {
            return true;
        }
        if (self::isFounder()) {
            return true;
            //todo 临时增加创始人私有管理插件权限
        } elseif (in_array($item, static::founderPermission())) {
            return false;
        }
        // 允许公众号管理员通过的路由
        $arr = [
            'survey.survey.index',
            'survey.survey.survey'
        ];
        // 临时解决公众号管理员能url访问超级管理员独有页面的问题
        if(!$item and !in_array(request()->getRoute(),$arr)){
            return false;
        }
        if (self::isOwner()) {
            return true;
        }
        if (self::isManager()) {
            return true;
        }
        if (self::checkNoPermission($item) === true) {
            return true;
        }
        return in_array($item, User::userPermissionCache());
    }

    /**
     * 检测是否存在白名单
     * @param $route
     * @return bool
     */
    public static function checkNoPermission($route)
    {
        $noPermissions = \Cache::get('noPermissions');
        if ($noPermissions === null) {
            $noPermissions = self::getNoPermissionList(\app\backend\modules\menu\Menu::current()->getItems());
            \Cache::put('noPermissions', $noPermissions);
        }
        if (in_array($route, $noPermissions)) {
            return true;
        }
        return false;
    }


    public static function founderPermission()
    {
        return [
            //插件管理
            'founder_plugins',
            'plugins_enable',
            'plugins_disable',
            'plugins_manage',
            'plugins_delete',
            'plugins_update',
            //
            'shop_upgrade',
            //队列管理
            'supervisor',
            'supervisord_supervisord_index',
            'supervisord_supervisord_store',
            'supervisord_supervisord_queue',
            //站点设置
            'site_setting',
            'site_setting.index',
            'site_setting.store',
            'site_setting.queue',
            'site_setting.physics_path',
            'site_setting.redis_config',
            'site_setting.mongoDB_config',
            'cache_setting',
            'setting_shop_log',
            'trojan',
            //工单管理
            'work_order',
            'work_order_store_page',
            'work_order_details',

            //系统工具
            'site_setting',
            'supervisord_supervisord_index',
            'supervisord_supervisord_store',
            'site_setting.index',
            'site_setting.queue',
            'site_setting.physics_path',
            'site_setting.redis_config',
            'site_setting.mongoDB_config',
            'site_setting.store',
            'cache_setting',
            'setting_shop_log',
            'trojan',

            //系统更新
            'setting_shop_update',

            //插件管理
            'plugins.get-plugin-data',
            'plugins_enable',
            'plugins_disable',
            'plugins_manage',
            'plugins_delete',
            'plugins_update',

            //安装应用
            'install_plugins',
        ];
    }

    /**
     * 获取权限白名单
     * @param $menus
     * @return array
     */
    public static function getNoPermissionList($menus)
    {
        $noPermissions = [];
        if ($menus) {
            foreach ($menus as $key => $m) {
                if (!isset($m['permit']) || (isset($m['permit']) && !$m['permit'])) {
                    $noPermissions[] = $key;
                }
                if (isset($m['child']) && $m['child']) {
                    $noPermissions = array_merge($noPermissions, self::getNoPermissionList($m['child']));
                }
            }
        }
        return $noPermissions;
    }

    /**
     * 是否是创始人
     * @return bool
     */
    public static function isFounder()
    {
        return \YunShop::app()->role === 'founder' && \YunShop::app()->isfounder === true;
    }

    /**
     * 是否是主管理员
     * @return bool
     */
    public static function isOwner()
    {
        return \YunShop::app()->role === 'owner';
    }

    /**
     * 是否是管理员
     * @return bool
     */
    public static function isManager()
    {
        return \YunShop::app()->role === 'manager';
    }

    /**
     * 是否是操作员
     * @return bool
     */
    public static function isOperator()
    {
        return \YunShop::app()->role === 'operator';
    }
}
