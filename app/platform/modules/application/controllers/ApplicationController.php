<?php

namespace app\platform\modules\application\controllers;

use app\common\events\DeleteAccountEvent;
use app\common\facades\Setting;
use app\common\models\Option;
use app\platform\controllers\BaseController;
use app\platform\modules\application\models\UniacidApp;
use app\common\helpers\Cache;
use app\platform\modules\pluginsSetMeal\models\PluginsMealModel;
use app\platform\modules\pluginsSetMeal\models\pluginsMealPlatform;
use app\platform\modules\system\models\SystemSetting;
use app\platform\modules\user\models\AdminUser;
use app\platform\modules\application\models\AppUser;
use Illuminate\Support\Facades\DB;
use app\backend\modules\member\models\Member;
use app\backend\modules\member\models\McMappingFans;
use app\common\models\MemberMiniAppModel;
use app\common\models\MemberWechatModel;
use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\member\models\MemberUnique;
use app\backend\modules\goods\models\Goods;
use app\backend\modules\order\models\Order;
use app\backend\modules\menu\Menu;
use app\common\modules\wechat\models\Rule;
use app\common\modules\wechat\models\RuleKeyword;
use Illuminate\Foundation\Bus\DispatchesJobs;
use app\Jobs\deleteUniacidColumnsJob;


class ApplicationController extends BaseController
{
    protected $key = 'application';

    use DispatchesJobs;

    public function index()
    {
        $search = request()->search;
        $app = new UniacidApp();
        $ids = self::checkRole();
        if (\Auth::guard('admin')->user()->uid != 1) {
            if (!is_array($ids)) {
                return $this->errorJson($ids);
            }
            $list = $app->select('id', 'name', 'img', 'validity_time', 'status', 'is_top')
                ->whereIn('id', $ids)->where('status', 1)
                ->search($search)
                ->orderBy('is_top', 'desc')
                ->orderBy('topped_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate()
                ->toArray();
        } else {
            $list = $app->select('id', 'name', 'img', 'validity_time', 'status', 'admin_is_top as is_top')
                ->where('status', 1)
                ->search($search)
                ->orderBy('admin_is_top', 'desc')
                ->orderBy('admin_topped_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate()
                ->toArray();
        }
        foreach ($list['data'] as $key => &$value) {
            $value['img'] = yz_tomedia($value['img']);
            if ($value['validity_time'] == 0) {
                $list['data'][$key]['validity_time'] = intval($value['validity_time']);
            } else {
                //到期前一周的时间  当前+1 直到 +7 小于等于 $value['validity_time']
                $week = date('W');
                $nowstamp = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $time_week = date('W', $value['validity_time']);
                if ((date('W', strtotime('+1 week')) == $time_week) || (date('W') == $time_week && $value['validity_time'] >= $nowstamp)) {
                    $list['data'][$key]['is_expire'] = 1;  //到期前一周
                }
                if ($value['validity_time'] != 0 && $value['validity_time'] < $nowstamp) {
                    $list['data'][$key]['is_expire'] = 2;  //已经到期
                }
                if ($value['validity_time'] === 0 || (date('W', strtotime('+1 week') - $time_week > 1) && $value['validity_time'] > $nowstamp)) {
                    $list['data'][$key]['is_expire'] = 0;
                }
                $list['data'][$key]['validity_time'] = date('Y-m-d', $value['validity_time']);
            }
        }
        return $this->successJson('获取成功', $list);
    }

    public static function checkRole()
    {
        $ids = [];
        $uid = \Auth::guard('admin')->user()->uid;

        $user = AdminUser::find($uid);

        $appUser = AppUser::where('uid', $uid)->get();

        if (!$user || !$appUser || $user->type == 3) {
            return '您无权限查看平台应用';
        }

        if ($user->endtime != 0 && $user->endtime < time()) {
            return '您的账号已过期';
        }

        foreach ($appUser->toArray() as $k => $v) {
            $ids[] = $v['uniacid'];
        }

        $app = UniacidApp::where('creator', $uid)->get();

        if ($app) {

            foreach ($app as $key => $value) {

                $ids[] = $value['id'];
            }
        }
        return $ids;
    }

    public function checkAddRole()
    {
        //判断用户是否有权限添加平台
        $uid = \Auth::guard('admin')->user()->uid;

        $num = UniacidApp::withTrashed()->where('creator', $uid)->count();

        $realnum = AdminUser::find($uid)->application_number;

        if ($uid != 1 && $num >= $realnum) {
            return $this->errorJson('您无权限添加平台');
        }
        return $this->successJson('可以添加平台');
    }

    public function add()
    {
        $app = new UniacidApp();

        //判断用户是否有权限添加平台
        $uid = \Auth::guard('admin')->user()->uid;

        $num = UniacidApp::withTrashed()->where('creator', $uid)->count();

        $adminUser = AdminUser::find($uid);

        if ($uid != 1 && $num >= $adminUser->application_number) {
            return $this->errorJson('您无权限添加平台');
        }

        $set = SystemSetting::settingLoad('basicsetting', 'basic_setting');

        if (!empty($set) and $set['type'] != 0) {
            if ($set['type'] == 1 and request()->input('validity_time') > $adminUser->endtime and $adminUser->endtime != 0) {
                return $this->errorJson('平台期限不能超过管理员有效期');
            }elseif ($set['type'] == 2 and request()->input('validity_time') > strtotime("+ {$set['endtime']} day") and $adminUser->endtime != 0){
                return $this->errorJson('平台期限不能超过指定效期');
            }
        }

        if (!request()->input()) {
            return $this->errorJson('请输入参数');
        }
        $platform = [
            'img' => request()->input('img'),
            'name' => request()->input('name'),
            'validity_time' => request()->input('validity_time')
        ];


        $data = $this->fillData($platform);

        $id = $app->insertGetId($data);

        if ($id) {
            if ($uid != 1) {
                // 新框架角色表插入数据
                AppUser::create([
                    'role' => 'manager',
                    'uid' => $uid,
                    'uniacid' => $id
                ]);
            }

            $up = UniacidApp::where('id', $id)->update(['uniacid' => $id]);

            if (!$up) {
                \Log::info('平台添加修改uniacid字段失败, id为', $id);
            }

            //添加商城key
            Setting::$uniqueAccountId = $id;
            $upgrade = Setting::get('shop.key');

            if (empty($upgrade['key']) && empty($upgrade['secret'])) {
                $platformShopValue = Setting::getNotUniacid('platform_shop.key');

                Setting::set('shop.key', $platformShopValue);

                \Cache::forget('app_auth' . $id);
            }

            //更新缓存
            if ($this->enabledPlugins($id,request()->input('plugins_meal_id'))){
                return $this->successJson('添加成功,套餐启动成功');
            }
            return $this->successJson('添加成功,套餐启动失败');

        } else {

            return $this->errorJson('添加失败');
        }
    }

    public function update()
    {

        $id = request()->id;

        $app = new UniacidApp();

        $info = $app->find($id);

        if (!$id || !$info) {
            return $this->errorJson('请选择应用');
        }

        if (request()->input()) {

            $data = $this->fillData(request()->input());
            $data['id'] = $id;
            $data['uniacid'] = $id;

            $app->fill($data);

            $validator = $app->validator($data);

            if ($validator->fails()) {

                return $this->errorJson($validator->messages());

            } else {

                if ($app->where('id', $id)->update($data)) {
                    //更新缓存
                    // Cache::put($this->key . ':' . $id, $app->find($id), $data['validity_time']);
                    if($this->enabledPlugins($id,request()->input('plugins_meal_id'))){
                        return $this->successJson('修改成功');
                    }
                    return $this->successJson('修改成功 ,但套餐使用失败');
                } else {

                    return $this->errorJson('修改失败');
                }
            }
        }
    }

    private function enabledPlugins($uniacid, $plugins_meal_id)
    {
        if (empty($uniacid) or empty($plugins_meal_id)) {
            return false;
        }
        $pluginsMeal = (new PluginsMealModel())->getPluginsMealList($plugins_meal_id);
        $pluginsList = Option::where('uniacid', $uniacid)->pluck('option_name')->toArray();

        if (!$pluginsMeal) {
            return false;
        }
        Menu::flush();
        $plugins = array_keys(app('plugins')->getPlugins()->toArray());
        \Setting::$uniqueAccountId = $uniacid;
        \YunShop::app()->uniacid = $uniacid;
        $pluginManager = app('app\common\services\PluginManager');
        $afterPluginsCheck = ['store-alone-temp']; //todo 某些插件可能需要启动别的先驱插件才能启动，暂时它放在这处理
        $afterPluginsList = []; //后启动插件数组
        try {
            $pluginsMealPlatform = PluginsMealPlatform::where('uniacid', $uniacid)->first();
            if ($pluginsMealPlatform->plugins_meal_id == $plugins_meal_id) {
                return true;
            }
            if (!$pluginsMealPlatform ) {
                if ($pluginsList) {
                    $pluginManager->disableMeal($uniacid);//关闭所有插件
                    foreach ($pluginsList as $plugin) {
                        $pluginManager->dispatchEvent($plugin);
                    }
                }
                foreach ($pluginsMeal[0]['plugins'] as $plugin) {
                    if (in_array($plugin, $plugins)) {
                        if (in_array($plugin, $afterPluginsCheck)) {
                            $afterPluginsList[] = $plugin;
                            continue;
                        }
                        $pluginManager->enableMeal($plugin);  //启用插件套餐
                    }
                }
                if (!empty($afterPluginsList)) {
                    \Artisan::call('config:cache');  //todo 必须刷新缓存，否则无法启动
                    \Cache::flush();
                    foreach ($afterPluginsList as $item) {
                        $pluginManager->enableMeal($item);  //启用插件套餐
                    }
                }
                (new pluginsMealPlatform())->create([
                    'uniacid' => $uniacid,
                    'plugins_meal_id' => $plugins_meal_id
                ]);
            } else {
                $pluginManager->disableMeal($uniacid);//关闭所有插件
                foreach ($pluginsList as $plugin) {
                    $pluginManager->dispatchEvent($plugin);
                }
                foreach ($pluginsMeal[0]['plugins'] as $plugin) {
                    $plugin = str_replace('_', '-', $plugin);
                    if (in_array($plugin, $plugins)) {
                        if (in_array($plugin, $afterPluginsCheck)) {
                            $afterPluginsList[] = $plugin;
                            continue;
                        }
                        $pluginManager->enableMeal($plugin);  //启用插件套餐
                    }
                }
                if (!empty($afterPluginsList)) {
                    \Artisan::call('config:cache');  //todo 必须刷新缓存，否则无法启动
                    \Cache::flush();
                    foreach ($afterPluginsList as $item) {
                        $pluginManager->enableMeal($item);  //启用插件套餐
                    }
                }
                PluginsMealPlatform::where('uniacid', $uniacid)->update(['plugins_meal_id' => $plugins_meal_id]);
            }

            \Artisan::call('config:cache');
            \Cache::flush();

            return true;

        } catch (\Exception $e) {
            \Artisan::call('config:cache');
            \Cache::flush();
            return false;
        }
    }

    public function getMessage()
    {
        if (\YunShop::app()->uid == 1){
            $plugin = PluginsMealModel::orderBy('order_by','desc')->get()->toArray();
        }else {
            $plugin = PluginsMealModel::where('state',1)->orderBy('order_by','desc')->get()->toArray();
        }

        $uid = \Auth::guard('admin')->user()->uid;
        $adminUser = AdminUser::find($uid);

        $set = SystemSetting::settingLoad('basicsetting', 'basic_setting');
        $data['type'] = $set['type'];


        switch ($set['type']){
            case 0:
                $data['validity_time'] = 0;
                break;
            case 1:
                $data['validity_time'] = $adminUser->endtime;
                break;
            case 2:
                $data['validity_time'] = strtotime("+ {$set['endtime']} day");
                break;

        }

        $data['plugin'] = $plugin;

        return $this->successJson('ok', $data);
    }

    public function getApp()
    {
        $id = request()->id;

        $app = new UniacidApp();

        $info = $app->find($id);

        $info['isfounder'] = intval(\YunShop::app()->isfounder);

        $info['plugins_meal_id'] = pluginsMealPlatform::where('uniacid', $id)->first()->plugins_meal_id;

        if (\YunShop::app()->uid == 1) {
            $info['plugin'] = PluginsMealModel::orderBy('order_by', 'desc')->get()->toArray();
        } else {
            $info['plugin'] = PluginsMealModel::where('state', 1)->orderBy('order_by', 'desc')->get()->toArray();
        }

        if (!$id || !$info) {
            return $this->errorJson('获取失败');
        }
        return $this->successJson('获取成功', $info);
    }

    //加入回收站 删除
    public function delete()
    {
        $id = request()->id;

        $info = UniacidApp::withTrashed()->find($id);

        if (!$id || !$info) {
            return $this->errorJson('请选择要修改的应用');
        }
        if ($info->deleted_at) {
            //强制删除相关会员信息
            $this->forceDel($info);
            // Cache::forget($this->key . ':' . $id);
        } else {

            if (!$info->delete()) {
                return $this->errorJson('操作失败');
            }
            UniacidApp::withTrashed()->where('id', $id)->update(['status' => 0]);

            // Cache::put($this->key . ':' . $id, UniacidApp::find($id));
        }

        return $this->successJson('操作成功');
    }

    //强制删除平台关联数据
    public function forceDel($info)
    {
        $uniacid = $info->uniacid;
        \YunShop::app()->uniacid = $uniacid;
        event(new DeleteAccountEvent($uniacid));
        DB::transaction(function () use ($uniacid) {
            if (!empty($uniacid)) {
                //删除yz_uniacid_app
                UniacidApp::where('uniacid',$uniacid)->forceDelete();
                //删除会员 mc_member
                Member::where('uniacid',$uniacid)->forceDelete();
                //小程序会员表  yz_member_mini_app
                MemberMiniAppModel::where('uniacid',$uniacid)->forceDelete();
                //app会员表 yz_member_wechat
                MemberWechatModel::where('uniacid',$uniacid)->forceDelete();
                //删除微擎mc_mapping_fans 表数据
                McMappingFans::where('uniacid',$uniacid)->forceDelete();
                //清空 yz_member 关联
                MemberShopInfo::where('uniacid',$uniacid)->forceDelete();
                //强制删除 yz_member_unique
                MemberUnique::where('uniacid',$uniacid)->forceDelete();
                //强制删除yz_goods
                Goods::where('uniacid',$uniacid)->forceDelete();
                //强制删除yz_order
                Order::where('uniacid',$uniacid)->forceDelete();
                //强制删除 yz_wechat_rule
                Rule::where('uniacid',$uniacid)->forceDelete();
                //强制删除 ims_yz_wechat_rule
                RuleKeyword::where('uniacid',$uniacid)->forceDelete();
                //删除yz_wechat_menu
                if(app('plugins')->isEnabled('wechat')){
                    \Yunshop\Wechat\common\model\Menu::where('uniacid',$uniacid)->forceDelete();
                }

                $tables = DB::select("SELECT DISTINCT TABLE_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'uniacid'");
                $tables = array_column($tables, 'TABLE_NAME');
                foreach ($tables as $v) {
                    $this->dispatch(new deleteUniacidColumnsJob($v, $uniacid));
                }
            }
        });

        \Log::info('------删除平台关联会员数据------', [$uniacid, \Auth::guard('admin')->user()]);
    }

    //启用禁用或恢复应用
    public function switchStatus()
    {

        $id = request()->id;

        $info = UniacidApp::withTrashed()->find($id);

        if (!$id || !$info) {
            return $this->errorJson('请选择要修改的应用');
        }

        if (request()->status) {
            //修改状态
            $res = UniacidApp::where('id', $id)->update(['status' => $info->status == 1 ? 0 : 1]);
        }

        if (request()->url) {
            //修改应用跳转链接
            $res = UniacidApp::where('id', $id)->update(['url' => filter_var(trim(request()->url), FILTER_VALIDATE_URL)]);
        }

        if ($info->deleted_at) {

            //从回收站中恢复应用
            $res = UniacidApp::withTrashed()->where('id', $id)->restore();
            $info->update(['status' => 1]);
        }

        if ($res) {

            return $this->successJson('操作成功');
        } else {
            return $this->errorJson('操作失败');
        }
    }

    //回收站 列表
    public function recycle()
    {
        $search = request()->search;

        $app = new UniacidApp();

        if (\Auth::guard('admin')->user()->uid != 1) {

            $list = $app->onlyTrashed()->where('creator', \Auth::guard('admin')->user()->uid)->search($search)->orderBy('id', 'desc')->paginate()->toArray();

        } else {

            $list = $app
                ->onlyTrashed()
                ->search($search)
                ->orderBy('id', 'desc')
                ->paginate()
                ->toArray();
        }


        foreach ($list['data'] as $key => $value) {

            if ($value['validity_time'] == 0) {

                $list['data'][$key]['validity_time'] = intval($value['validity_time']);

            } else {

                $list['data'][$key]['validity_time'] = date('Y-m-d', $value['validity_time']);
            }
        }

        if ($list) {
            return $this->successJson('获取成功', $list);
        } else {
            return $this->errorJson('获取失败,暂无数据');
        }
    }

    private function fillData($data)
    {
        return [
            'img' => $data['img'] ?: 'http://www.baidu.com',
            'url' => $data['url'],
            'name' => $data['name'] ?: 'test',
            'kind' => $data['kind'] ?: '',
            'type' => $data['type'] ?: 2,
            'title' => $data['title'] ?: '',
            'description' => $data['description'] ?: '',
            'status' => $data['status'] ?: 1,
            'version' => $data['version'] ?: 0.00,
            'validity_time' => $data['validity_time'] ?: 0,
            'creator' => \Auth::guard('admin')->user()->uid,
        ];
    }

    public function setTop()
    {
        $id = request()->id;
        $info = UniacidApp::withTrashed()->find($id);
        if (!$id || !$info) {
            return $this->errorJson('请选择要置顶的应用');
        }
        if (\Auth::guard('admin')->user()->uid != 1) {
            if ($info->is_top) {
                //修改置顶状态
                $res = UniacidApp::where('id', $id)->update(['is_top' => 0]);
            } else {
                //非置顶状态--取消其他置顶--再置顶
                $ids = self::checkRole();
                UniacidApp::whereIn('id', $ids)->where('is_top', 1)->update(['is_top' => 0]);
                $res = UniacidApp::where('id', $id)->update(['is_top' => 1, 'topped_at' => time()]);
            }
        } else {
            //修改置顶状态
            if ($info->admin_is_top) {
                $res = UniacidApp::where('id', $id)->update(['admin_is_top' => 0, 'admin_topped_at' => '']);
            } else {
                UniacidApp::where('admin_is_top', 1)->update(['admin_is_top' => 0]);
                $res = UniacidApp::where('id', $id)->update(['admin_is_top' => 1, 'admin_topped_at' => time()]);
            }
        }

        if ($res) {
            return $this->successJson('操作成功');
        } else {
            return $this->errorJson('操作失败');
        }
    }
    public function basicSettings()
    {
        if (request()->isMethod("GET"))
        {
            $set = SystemSetting::settingLoad('basicsetting', 'basic_setting');

            if (empty($set)) {
                $set['type'] = 0;
            }

            return $this->successJson('ok', $set);
        }

        $data = request()->input('term_of_validity');
        if ($data) {
            $site = SystemSetting::settingSave($data, 'basicsetting', 'basic_setting');
            if ($site) {
                return $this->successJson('成功', '');
            } else {
                return $this->errorJson('失败', '');
            }
        }
    }
}
