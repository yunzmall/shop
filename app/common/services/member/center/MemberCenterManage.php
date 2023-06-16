<?php

namespace app\common\services\member\center;

use AlibabaCloud\SDK\Iot\V20180120\Models\CreateTopicRouteTableResponseBody\failureTopics;
use app\common\exceptions\AppException;
use app\common\models\Member;
use app\common\services\Session;
use app\framework\Repository\Collection;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\widgets\WidgetsConfig;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Yunshop\Commission\models\AgentLevel;
use Yunshop\Commission\models\Agents;
use Yunshop\Decorate\models\DecorateDefaultTabModel;
use Yunshop\Decorate\models\DecorateDefaultTemplateModel;
use Yunshop\Decorate\models\DecorateTempletModel;
use Yunshop\TeamDividend\admin\models\TeamDividendAgencyModel;

class MemberCenterManage extends Container
{
    public $type_one = [
        'essential_tool' => '必备工具',
        'service' => '便捷服务',
        'interactive' => '互动参与',
        'feature' => '特色业务',
        'business' => '商家合作',
        'my_assets' => '我的资产'
    ];
    public $type_two = [
        'tool' => '实用工具',
        'merchant' => '商家管理',
        'market' => '营销互动',
        'asset_equity' => '资产权益',
    ];

    public $view_type = 1;

    public $is_diy = false;

    public $allData;


    public function __construct()
    {
        $this->singleton('CenterCollection', function () {
            $collection = new Collection(array_merge([MemberCenterService::class], array_values(WidgetsConfig::getConfig('member_center'))));
            return $collection->transform(function ($class) {
                return new $class;
            });
        });
        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == 1) {
            //会员中心模版
            $view_set = DecorateTempletModel::getList(['is_default' => 1, 'type' => 1], '*', false);
            if ($view_set['code'] == 'member02') {
                $this->view_type = 2;
            }
        }
        //   $this->view_type = 2;

    }


    /**
     * @throws BindingResolutionException
     * @throws AppException
     */
    public function getData(): array
    {
        $designer = $this->getDesigner();
        if (!empty($designer)) {
            return [
                'designer' => $designer,
                'other' => $this->getOtherData(),
                'foot' => $this->getFootData()
            ];
        }
        return [
            'member_info' => $this->getMemberInfo(),
            'order' => $this->getOrderData(),
            'assets' => $this->getAssetsData(),
            'plugins' => $this->getViewTypeData(),
            'plugin_data' => $this->getPluginData(),
            'other' => $this->getOtherData(),
            'foot' => $this->getFootData()
        ];
    }

    /**
     * 获取会员信息
     * @param $grade_type
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \app\common\exceptions\AppException
     */
    private function getMemberInfo($grade_type = 1): array
    {
        $member = Member::current();
        $set = \Setting::get('shop.member');
        $data['member_id'] = $member['uid'];
        $data['avatar'] = $member['avatar_image'];
        $data['nickname'] = $member['nickname'];
        $data['invite_code_status'] = \Setting::get('shop.member.is_invite') ?: 0;;
        $data['invite_code'] = $member['yzMember']['invite_code'] ?? MemberModel::getInviteCode($member['uid']);
        $data['is_agent'] = $member['yzMember']['is_agent'] == 1 && $member['yzMember']['status'] == 2;
        $data['show_member_id'] = $set['show_member_id'] ?? 0;
        $data['jump_level_page'] = $set['display_page'] ? 1 : 0;
        $data['level_id'] = 0;
        $data['level_name'] = \Setting::get('shop.member')['level_name'] ?? '普通会员';
        $data['validity'] = '';
        if (!empty($member['yzMember']['level'])) {
            $data['level_id'] = $member['yzMember']['level']['id'];
            $data['level_name'] = $member['yzMember']['level']['level_name'];
        }
        if ($member['yzMember']['validity'] && $data['level_id'] > 0 && $set['level_type'] && $set['term']) {
            $data['validity'] = date('Y-m-d', strtotime($member['yzMember']['validity'] - 1 . ' days'));
        }
        $data['grade_type'] = 1;
        switch ($grade_type) {
            //经销商等级
            case 2:
                if (app('plugins')->isEnabled('team-dividend')) {
                    $agency_model = TeamDividendAgencyModel::getAgencyInfoByUid(\YunShop::app()->getMemberId());
                    $data['level_id'] = $agency_model->hasOneLevel->id ?: 0;
                    $data['level_name'] = $agency_model->hasOneLevel->level_name ?: '';
                    $data['grade_type'] = $grade_type;
                }
                break;
            //分销等级
            case 3:
                if (app('plugins')->isEnabled('commission')) {
                    $agency_model = Agents::getLevelByMemberId()
                        ->where('member_id', \YunShop::app()->getMemberId())
                        ->first();
                    if (empty($agency_model)) {
                        break;
                    }
                    $data['level_id'] = $agency_model->agentLevel->id ?: 0;
                    $data['level_name'] = $agency_model->agentLevel->name ?: AgentLevel::getDefaultLevelName();
                    $data['grade_type'] = $grade_type;
                }
                break;
        }
        $this->make('CenterCollection')->each(function ($class) use (&$data) {
            if (method_exists($class, 'getMemberInfo')) {
                $data = $class->getMemberInfo($data);
            }
        });
        return $data;
    }

    /**
     * 获取会员中心入口
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getCenterAllData(): array
    {
        if (isset($this->allData)) {
            return $this->allData;
        }
        $this->allData = [];
        $this->make('CenterCollection')->each(function ($class) {
            if (method_exists($class, 'getData')) {
                $data = $class->getData();
                $this->allData = array_merge($this->allData, $data);
            }
        });
        return $this->allData;
    }

    /**
     * 获取会员中心所有入口
     * @return array
     */
    private function getViewTypeData(): array
    {
        $data = $this->getCenterAllData();
        $method = "getViewTypeData$this->view_type";
        return $this->$method($data);
    }


    /**
     * 获取会员中心订单信息
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getOrderData(): array
    {
        $data = [];
        $this->make('CenterCollection')->each(function ($class) use (&$data) {
            if (method_exists($class, 'getOrderData')) {
                $data = array_merge($data, $class->getOrderData());
            }
        });
        return collect($data)->sortBy('weight')->sortBy('status')->values()->toArray();
    }

    /**
     * 获取会员中心资产信息
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getAssetsData(): array
    {
        $data = [];
        $this->make('CenterCollection')->each(function ($class) use (&$data) {
            if (method_exists($class, 'getAssetsData')) {
                $data = array_merge($data, $class->getAssetsData());
            }
        });

        return collect($data)->sortBy('weight')->sortBy('status')->values()->toArray();
    }

    /**
     * 获取会员中心模版1入口
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getViewTypeAllData1(): array
    {
        $data = $this->getCenterAllData();
        $where = 'weight_1';
        $sort = 'weight_1';
        $group = 'type_1';
        $result = [];
        $data = collect($data)->where($where, '>', 0)->sortBy($sort)->groupBy($group)->toArray();
        foreach ($this->type_one as $key => $name) {
            $result[] = [
                'name' => $name,
                'data' => $data[$key]
            ];

        }
        return $result;
    }

    /**
     * 获取会员中心模版1前19个入口
     * @param $data
     * @return array
     */
    private function getViewTypeData1($data): array
    {
        $where = 'default_weight';
        $sort = 'default_weight';
        $head = [];
        $this->make('CenterCollection')->each(function ($class) use (&$head) {
            if (method_exists($class, 'getPluginHead')) {
                $head = array_merge($head, $class->getPluginHead());
            }
        });
        $data = $this->getCenterAllData();
        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') && \Setting::get('decorate.mc_one_default.open_state')) {
            $body_key = DecorateDefaultTemplateModel::uniacid()
                ->templateKey(DecorateDefaultTemplateModel::TEMPLTAE_KEY['mc_one_default'])
                ->status(1)->orderBy('sort', 'ASC')
                ->orderBy('id', 'ASC')->limit(19)->pluck('key')->toArray();
            $body = collect($data)->whereIn('name', $body_key)->sortBy(function ($item) use ($body_key) {
                return array_search($item['name'], $body_key);
            })->values()->toArray();
        } else {
            $body = collect($data)->where($where, '>', 0)->sortBy($sort)->values()->toArray();
        }
        return [
            'head' => $head,
            'body' => array_slice($body, 0, 19)
        ];
    }


    /**
     * 获取会员中心模版2入口
     * @param $data
     * @return array
     */
    private function getViewTypeData2($data): array
    {
        $where = 'weight_2';
        $sort = 'weight_2';
        $group = 'type_2';
        $result = [];
        $data = collect($data)->where($where, '>', 0)->sortBy($sort)->groupBy($group)->toArray();
        foreach ($this->type_two as $key => $name) {
            $result[] = [
                'name' => $name,
                'data' => $data[$key]
            ];
        }
        return $result;
    }

    public function getPluginData(): array
    {
        $data = [];
        if ($this->view_type == 1) {
            $data = $this->getAllPluginData();
            if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') && \Setting::get('decorate.mc_one_default.open_state')) {
                $data = DecorateDefaultTabModel::formNav($data);
            }
        }
        return collect($data)->sortBy('sort')->values()->transform(function ($item, $key) {
            if ($key == 0) {
                $func = $item['code'];
                $item['data'] = app($item['class'])->$func();
            }
            return $item;
        })->toArray();
    }

    public function getAllPluginData(): array
    {
        $data = [];
        $this->make('CenterCollection')->each(function ($class) use (&$data) {
            if (method_exists($class, 'getPluginData')) {
                $data = array_merge($data, $class->getPluginData());
            }
        });
        return $data;
    }


    public function getPluginDataDetail($code)
    {
        $data = [];
        $this->make('CenterCollection')->each(function ($class) use (&$data) {
            if (method_exists($class, 'getPluginData')) {
                $data = array_merge($data, $class->getPluginData());
            }
        });
        $detail = collect($data)->where('code', $code)->first();
        return app($detail['class'])->$code();
    }


    private function getFootData()
    {
        $data['copyright_img'] = yz_tomedia(\Setting::get('shop.shop.copyrightImg')) ?: '';
        $data['copyright'] = \Setting::get('shop.shop.copyright') ?: '';
        $data['cat_adv_url'] = \Setting::get('shop.shop.cat_adv_url') ?: '';
        $data['small_cat_adv_url'] = \Setting::get('shop.shop.small_cat_adv_url') ?: '';
        return $data;
    }

    private function getOtherData()
    {
        $data = [];
        $this->make('CenterCollection')->each(function ($class) use (&$data) {
            if (method_exists($class, 'getOtherData')) {
                $data = array_merge($data, $class->getOtherData());
            }
        });
        return collect($data)->toArray();
    }

    private function getDesigner()
    {
        if (!app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') != 1) {
            return [];
        }
        //如果安装了新装修插件并开启插件
        $type = request()->input('type');
        switch ($type) {
            case 7:
                $pageType = 3;
                break;
            case 8:
                $pageType = 4;
                break;
            case 18:
                $pageType = 5;
                break;
            default:
                $pageType = $type;
        }
        if (request()->input('cps_h5')) {
            $pageType = 7;
        }
        $page = new \Yunshop\Decorate\frotend\IndexController();
        $page->page_type = $pageType;
        $page->page_scene = 2;
        $page->page_sort = 1;
        if ($type == 2) {
            $page->page_sort = 2;
        }
        $decorate = $page->getPage();
        if ($decorate) {
            $this->is_diy = true;
            $data = json_decode($decorate['datas'], true);
            $decorate['page_info'] = json_decode($decorate['page_info'], true);
            $center_data = $this->getViewTypeData2($this->getCenterAllData());
            $center_data = array_column($center_data, null, 'name');
            foreach ($data as &$value) {
                switch ($value['component_key']) {
                    //会员信息
                    case 'U_membertop':
                        $grade_type = $value['remote_data']['grade_type'] ?: 1;
                        $value['remote_data']['data'] = $this->getMemberInfo($grade_type);
                        break;
                    case 'U_memberorder':
                        $order_list = $this->getOrderData();
                        $order_list = array_column($order_list, NULL, 'diy_key');
                        foreach ($value['remote_data']['list'] as $key_list => &$list) {
                            $diy_key = $list['uikey'];
                            $order = $order_list[$diy_key]['data'];
                            if (empty($order)) {
                                unset($value['remote_data']['list'][$key_list]);
                                continue;
                            }
                            foreach ($list['remote_data']['list'] as &$order_status) {
                                $order_status['total'] = $order[$order_status['id'] - 1]['total'] ?? 0;
                                $order_status['class'] = $order[$order_status['id'] - 1]['class'] ?? 0;
                            }
                            unset($order_status);
                        }
                        unset($list);
                        $value['remote_data']['list'] = array_values($value['remote_data']['list']);
                        break;
                    case 'U_membertool':
                        $center = array_column($value['remote_data']['show_list'], 'name');
                        $images = array_column($value['remote_data']['show_list'],'image' ,'name');
                        $temp = $center_data['实用工具']['data'];
                        foreach ($temp as $key => &$list) {
                            $list['image'] = $images[$list['name']];
                            if (!in_array($list['name'], $center)) {
                                unset($temp[$key]);
                            }
                        }
                        $value['remote_data']['show_list'] = $temp;
                        unset($list);
                        break;
                    case 'U_membermarket':
                        $center = array_column($value['remote_data']['show_list'], 'name');
                        $images = array_column($value['remote_data']['show_list'],'image' ,'name');
                        $temp = $center_data['营销互动']['data'];
                        foreach ($temp as $key => &$list) {
                            $list['image'] = $images[$list['name']];
                            if (!in_array($list['name'], $center)) {
                                unset($temp[$key]);
                            }
                        }
                        $value['remote_data']['show_list'] = $temp;
                        unset($list);
                        break;
                    case 'U_membermerchant':
                        $center = array_column($value['remote_data']['show_list'], 'name');
                        $images = array_column($value['remote_data']['show_list'],'image' ,'name');
                        $temp = $center_data['商家管理']['data'];
                        foreach ($temp as $key => &$list) {
                            $list['image'] = $images[$list['name']];
                            if (!in_array($list['name'], $center)) {
                                unset($temp[$key]);
                            }
                        }
                        $value['remote_data']['show_list'] = $temp;
                        unset($list);
                        break;
                    case 'U_memberrights':
                        $center = array_column($value['remote_data']['show_list'], 'name');
                        $images = array_column($value['remote_data']['show_list'],'image' ,'name');
                        $temp = $center_data['资产权益']['data'];
                        foreach ($temp as $key => &$list) {
                            $list['image'] = $images[$list['name']];
                            if (!in_array($list['name'], $center)) {
                                unset($temp[$key]);
                            }
                        }
                        $value['remote_data']['show_list'] = $temp;
                        unset($list);
                        break;
                    case 'U_memberasset':
                        $asset_list = $this->getAssetsData();
                        $asset_list = array_column($asset_list, null, 'diy_key');
                        foreach ($value['remote_data']['show_list'] as $key => &$list) {
                            if ($asset = $asset_list[$list['value']]) {
                                $list = $asset;
                                if ($list['value'] instanceof \Closure) {
                                    $list['value'] = call_user_func($list['value'],$value['remote_data']);
                                }
                            } else {
                                unset($value['remote_data']['show_list'][$key]);
                            }
                        }
                        unset($list);
                        break;
                }
                $value['remote_data']['show_list'] = array_values($value['remote_data']['show_list']);
            }
            unset($value);
            $decorate['datas'] = $data;
            return $decorate;
        }
        return [];
    }


    public function isDiy(): bool
    {
        return $this->is_diy;
    }

    public function getDiyMemberData(): array
    {
        $data = $this->getCenterAllData();
        $where = 'weight_2';
        $sort = 'weight_2';
        $group = 'type_2';
        return collect($data)->where($where, '>', 0)->sortBy($sort)->groupBy($group)->toArray();
    }


}