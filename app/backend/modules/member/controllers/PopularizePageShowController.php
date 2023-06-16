<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2018/11/28
 * Time: 13:56
 */

namespace app\backend\modules\member\controllers;

use app\backend\modules\income\Income;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\helpers\Url;
use app\common\models\Menu;
use app\common\services\popularize\PopularizePageShowFactory;

class PopularizePageShowController extends BaseController
{
    /**
     * 加载模板
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        return view('member.popularize.index', [])->render();
    }

    //微信
    public function wechatSet()
    {
        $info = Setting::get("popularize.wechat");
        $set = request()->input('set');

        if (!empty($set)) {
            foreach ($set['plugin'] as $key => $value) {
                $set[$key] = $value;
            }
            unset($set['plugin']);
            if (Setting::set("popularize.wechat", $set)) {
                return $this->successJson('保存成功', ['data' => true]);
            } else {
                throw new ShopException('保存失败');
            }
        }
        return $this->successJson('ok', $this->getResult($info));
    }

    //微信小程序
    public function miniSet()
    {
        $info = Setting::get("popularize.mini");

        $set = request()->input('set');
        if (!empty($set)) {
            foreach ($set['plugin'] as $key => $value) {
                $set[$key] = $value;
            }
            unset($set['plugin']);
            if (Setting::set("popularize.mini", $set)) {
                return $this->successJson('保存成功', ['data' => true]);
            } else {
                throw new ShopException('保存失败');
            }
        }
        $info['min-app'] = 1;

        return $this->successJson('ok', $this->getResult($info, 1));
    }

    //手机浏览器 pc
    public function wapSet()
    {
        $info = Setting::get("popularize.wap");

        $set = request()->input('set');
        if (!empty($set)) {
            foreach ($set['plugin'] as $key => $value) {
                $set[$key] = $value;
            }
            unset($set['plugin']);
            if (Setting::set("popularize.wap", $set)) {
                return $this->successJson('保存成功', ['data' => true]);
            } else {
                throw new ShopException('保存失败');
            }
        }

        return $this->successJson('ok', $this->getResult($info));
    }

    //app
    public function appSet()
    {
        $info = Setting::get("popularize.app");

        $set = request()->input('set');
        if (!empty($set)) {
            foreach ($set['plugin'] as $key => $value) {
                $set[$key] = $value;
            }
            unset($set['plugin']);
            if (Setting::set("popularize.app", $set)) {
                return $this->successJson('保存成功', ['data' => true]);
            } else {
                throw new ShopException('保存失败');
            }
        }

        return $this->successJson('ok', $this->getResult($info));
    }

    //支付宝
    public function alipaySet()
    {
        $info = Setting::get("popularize.alipay");

        $set = request()->input('set');
        if (!empty($set)) {
            foreach ($set['plugin'] as $key => $value) {
                $set[$key] = $value;
            }
            unset($set['plugin']);
            if (Setting::set("popularize.alipay", $set)) {
                return $this->successJson('保存成功', ['data' => true]);
            } else {
                throw new ShopException('保存失败');
            }
        }

        return $this->successJson('ok', $this->getResult($info));
    }

    /**
     * 获取商城开启的插件
     * @return array 开启的插件页面路由与名称
     */
    protected function getData()
    {
        $lang_set = $this->getLangSet();

        $config = $this->getIncomePageConfig();

        $plugins = $this->getPlugins();

        foreach ($config as $key => $item) {

            $incomeFactory = new PopularizePageShowFactory(new $item['class'], $lang_set);

            if ($plugins[$incomeFactory->getMark()]) {
                $array[] = [
                    'url' => $plugins[$incomeFactory->getMark()],
                    'title' => $incomeFactory->getTitle(),
                    'mark' => $incomeFactory->getMark(),
                    'status' => is_array($plugins[$incomeFactory->getMark()]) ? 1 : 0,
                    'type' => $item['type']
                ];
            } else {
                $array[] = [
                    'url' => $incomeFactory->getAppUrl(),
                    'title' => $incomeFactory->getTitle(),
                    'mark' => $incomeFactory->getMark(),
                    'status' => 0,
                    'type' => $item['type']
                ];
            }
        }
        return $array;
    }

    //当收入页面显示的前端路由有两个时,需要在把俩个路由都添加进来
    // \Config::set(['popularize_page_show.收入页面类的getMark()方法返回的值' => 路由数组);
    protected function getPlugins()
    {
        $popularize_page_show = [
            'area_dividend' => 'regionalAgencyCenter',
            'store_cashier' => 'cashier',
            'store_withdraw' => 'storeManage',
            'hotel_cashier' => 'HotelCashier',
            'hotel_withdraw' => 'HotelManage',
            'merchant' => 'enterprise_index',
            'micro' => 'microShop_ShopKeeperCenter',
        ];

        $plugin = \app\common\modules\shop\ShopConfig::current()->get('popularize_page_show') ?: [];

        return array_merge($popularize_page_show, $plugin);
    }

    /**
     * 生成js文件给前端用
     */
    protected function toJson()
    {
        //放弃使用这个方法，原因无法匹配多个公众号
        return;

        $all_set = Setting::getByGroup("popularize");

        $data = [
            'wechat' => [
                'vue_route' => !empty($all_set['wechat']['vue_route']) ? $all_set['wechat']['vue_route'] : [],
                'url' => !empty($all_set['wechat']['callback_url']) ? $all_set['wechat']['callback_url'] : '',
            ],
            'mini' => [
                'vue_route' => !empty($all_set['mini']['vue_route']) ? $all_set['mini']['vue_route'] : [],
                'url' => !empty($all_set['mini']['callback_url']) ? $all_set['mini']['callback_url'] : '',
            ],
            'wap' => [
                'vue_route' => !empty($all_set['wap']['vue_route']) ? $all_set['wap']['vue_route'] : [],
                'url' => !empty($all_set['wap']['callback_url']) ? $all_set['wap']['callback_url'] : '',
            ],
            'app' => [
                'vue_route' => !empty($all_set['app']['vue_route']) ? $all_set['app']['vue_route'] : [],
                'url' => !empty($all_set['app']['callback_url']) ? $all_set['app']['callback_url'] : '',
            ],
            'alipay' => [
                'vue_route' => !empty($all_set['alipay']['vue_route']) ? $all_set['alipay']['vue_route'] : [],
                'url' => !empty($all_set['alipay']['callback_url']) ? $all_set['alipay']['callback_url'] : '',
            ],
        ];
        $string = json_encode($data);
        $sj = date('Y-m-d H:i:s', time());
        $json_str = <<<json
//update $sj
let popularize = {$string};
if (typeof define === "function") {
    define(popularize)
} else {
    window.\$popularize = popularize;
}
json;

        $path = 'static' . DIRECTORY_SEPARATOR . 'yunshop' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'popularize' . DIRECTORY_SEPARATOR;
        $absolute_file = $path . 'popularize_' . \YunShop::app()->uniacid . '.js';

        // 生成目录
        if (!is_dir(base_path($path))) {
            mkdir(base_path($path), 0777);
        }

        return file_put_contents(base_path($absolute_file), $json_str);
    }

    /**
     * 收入页面配置 config
     *
     * @return mixed
     */
    private function getIncomePageConfig()
    {
        return Income::current()->getPageItems();
    }


    /**
     * 获取商城中的插件名称自定义设置
     *
     * @return mixed
     */
    private function getLangSet()
    {
        $lang = \Setting::get('shop.lang', ['lang' => 'zh_cn']);

        return $lang[$lang['lang']];
    }

    private function getItems()//获取所有插件
    {
        $config = \app\backend\modules\income\Income::current()->getPageItems();//获取所有插件
        $data = [];
        foreach ($config as $key => $item) {
            $incomeFactory = new $item['class'];
            $data[$key]['value'] = $incomeFactory->getMark();//获取收类型的名称
            $data[$key]['name'] = $incomeFactory->getTitle();
        }
        return $data;
    }

    public function getPluginType()
    {
        return [
            'dividend' => '入口类',
            'industry' => '行业类',
            'marketing' => '营销类',
            'business_management' => '企业管理类',
            'tool' => '工具类',
            'recharge' => '生活充值',
            'api' => '接口类',
            'store' => '门店应用类',
            'blockchain' => '区块链类',
            'douyin' => '抖音类应用',
        ];
    }

    public function setArray($info)
    {
        $array = $this->getData();
        foreach ($array as $value) {
            if ($value['type'] == 'dividend') {
                $dividend[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'industry') {
                $industry[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'marketing') {
                $marketing[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'business_management') {
                $business_management[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'tool') {
                $tool[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'recharge') {
                $recharge[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'api') {
                $api[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'store') {
                $store[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
            if ($value['type'] == 'blockchain') {
                $blockchain[] = [
                    'url' => $value['url'],
                    'name' => $value['title'],
                    'type' => $value['type'],
                    'status' => in_array($value['url'], $info['vue_route']) ? 1 : 0,
                    'mark' => $value['mark']
                ];
            }
        }
        $not_plugin = [
            'dividend' => $dividend,
            'industry' => $industry,
            'marketing' => $marketing,
            'business_management' => $business_management,
            'tool' => $tool,
            'recharge' => $recharge,
            'api' => $api,
            'store' => $store,
            'blockchain' => $blockchain
        ];
        return $not_plugin;
    }

    public function getResult($info, $item = 0)
    {
        $not_plugin = $this->setArray($info);
        $array1 = $this->getItems();
        foreach ($array1 as $v) {
            $plugin[] = [
                'status' => !empty($info[$v['value']]) ? $info[$v['value']] : 0,
                'name' => $v['name'],
                'value' => $v['value'],
            ];
        }
        $basics = [
            'background_color' => $info['background_color'],
            'callback_url' => $info['callback_url'],
            'is_show_unable' => $info['is_show_unable'],
            'show_member_id' => $info['show_member_id'],
            'extension' => in_array('extension', $info['vue_route']) ? 1 : 0,
            'not_plugin' => $not_plugin,
            'plugin_type' => $this->getPluginType()
        ];

        if ($item == 1) {
            $basics['small_extension_link'] = $info['small_extension_link'];
        }

        return [
            'plugin' => $plugin,
            'basics' => $basics,
        ];
    }
}