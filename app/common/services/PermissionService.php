<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 08/03/2017
 * Time: 09:39
 */

namespace app\common\services;


use app\common\exceptions\ShopException;
use app\common\helpers\Cache;
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
            //todo 临时增加创始人私有管理插件权限,非创始人用户地址栏访问创始人私有页面时$item为null
        } elseif (
            in_array(request()->getRoute(), static::founderPermission())
            or
            in_array($item, static::founderPermission())
        ) {
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
        $key = 'noPermissions'.\YunShop::app()->uid; //key拼上uid，放止有的如供应商菜单有根据登录的账号资格来设置菜单的
        if (Cache::has($key)) {
            $noPermissions = Cache::get($key);
        } else {
            $noPermissions = self::getNoPermissionList(\app\backend\modules\menu\Menu::current()->getItems());
            Cache::put($key, $noPermissions,120);
        }
        if (in_array($route, $noPermissions)) {
            return true;
        }
        return false;
    }

    /**
     * 创始人私有的页面与功能
     * @return string[]
     */
    public static function founderPermission()
    {
        return [
            // 插件管理
            // route
            'plugins.get-plugin-data',
            'plugins.enable',
            'plugins.disable',
            'plugins.manage',
            'plugins.delete',
            'plugins.update',
            // key
            'founder_plugins',
            'plugins_enable',
            'plugins_disable',
            'plugins_manage',
            'plugins_delete',
            'plugins_update',

            // 系统工具
            // route
            'supervisord.supervisord.index',
            'supervisord.supervisord.index',
            'supervisord.supervisord.store',
            'siteSetting.index.index',
            'siteSetting.index.queue',
            'siteSetting.index.physics-path',
            'siteSetting.index.redis-config',
            'siteSetting.index.mongoDB-config',
            'site_setting.store.index',
            'setting.cache.index',
            'setting.cron_log.index',
            'setting.trojan.check',
            'setting.trojan.del',
            // key
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
            'work_order_store_page',

            // 工单管理
            // route
            'setting.work-order.index',
            'setting.work-order.store-page',
            'setting.work-order.details',
            // key
            'work_order',
            'work_order_store_page',
            'work_order_details',

            // 系统更新
            // route
            'update.index',
            // key
            'setting_shop_update',

            // 安装应用
            // route
            'plugins.jump',// 这个是中转方法，因为还要提示信息
            'plugin.plugins-market.Controllers.new-market.show',
            // key
            'install_plugins',

            // 清除小程序粉丝
            'plugin.min-app.admin.clear',
            'plugin.min-app.admin.clear-fan'
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
