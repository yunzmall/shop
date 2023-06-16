<?php

/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/1/5
 * Time: 9:34
 */

namespace app\backend\modules\member\services;

use app\common\facades\Setting;

class FansItemService
{
    public function setFansItem($list)
    {
        foreach ($list['data'] as $key => $item) {
            if (isset($item['has_one_unique'])) {
                $list['data'][$key]['fans_item'] = substr($item['has_one_unique']['unionid'], 0, 4) . '**' . substr($item['has_one_unique']['unionid'], -6);
            }
            if (isset($item['has_one_mini_app'])) {
                $list['data'][$key]['fans_item'] = substr($item['has_one_mini_app']['openid'], 0, 4) . '**' . substr($item['has_one_mini_app']['openid'], -6);
            }
            if (isset($item['has_one_fans'])) {
                $list['data'][$key]['fans_item'] = substr($item['has_one_fans']['openid'], 0, 4) . '**' . substr($item['has_one_fans']['openid'], -6);
            }
            if (!empty($item['mobile'])) {
                $list['data'][$key]['fans_item'] = substr_replace($item['mobile'],'*******',2,7);;
            }
            if (!empty($item['nickname'])) {
                $list['data'][$key]['fans_item'] = $item['nickname'];
            }
            if (empty($item['avatar']) && !empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['avatar'] = yz_tomedia(Setting::get('shop.member')['headimg_url']);
            }
            if (empty($item['avatar']) && empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['avatar'] = yz_tomedia(Setting::get('shop.shop')['logo']);
            }
            if (empty($item['yz_member']['agent']['avatar']) && empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['yz_member']['agent']['avatar'] = yz_tomedia(Setting::get('shop.shop')['logo']);
            }
        }
        return $list;
    }

    public function setAgentFansItem($list,$team_dividend_levels = [])
    {
        foreach ($list['data'] as $key => $item) {
            if (isset($item['has_one_unique'])) {
                $list['data'][$key]['has_one_child_member']['fans_item'] = substr($item['has_one_unique']['unionid'], 0, 4) . '**' . substr($item['has_one_unique']['unionid'], -6);
            }
            if (isset($item['has_one_mini_app'])) {
                $list['data'][$key]['has_one_child_member']['fans_item'] = substr($item['has_one_mini_app']['openid'], 0, 4) . '**' . substr($item['has_one_mini_app']['openid'], -6);
            }
            if (isset($item['has_one_fans'])) {
                $list['data'][$key]['has_one_child_member']['fans_item'] = substr($item['has_one_fans']['openid'], 0, 4) . '**' . substr($item['has_one_fans']['openid'], -6);
            }
            if (!empty($item['has_one_child_member']['mobile'])) {
                $list['data'][$key]['has_one_child_member']['fans_item'] = $item['has_one_child_member']['mobile'];
            }
            if (!empty($item['has_one_child_member']['nickname'])) {
                $list['data'][$key]['has_one_child_member']['fans_item'] = $item['has_one_child_member']['nickname'];
            }

            if (empty($item['has_one_child_member']['avatar']) && !empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['avatar'] = yz_tomedia(Setting::get('shop.member')['headimg_url']);
            }
            if (empty($item['has_one_child_member']['avatar']) && empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['avatar'] = yz_tomedia(Setting::get('shop.shop')['logo']);
            }

            $list['data'][$key]['team_dividend_level_name'] = '';
            $list['data'][$key]['order_price_sum'] = $item['order_price_sum'] ? : 0;

            if ($team_dividend_levels && $item['has_one_child_team_dividend']) {
                $list['data'][$key]['team_dividend_level_name'] = $team_dividend_levels[$item['has_one_child_team_dividend']['level']]['level_name'] ?: '';
            }

        }
        return $list;
    }

    public function setParentFansItem($list)
    {
        foreach ($list['data'] as $key => $item) {
            if (isset($item['has_one_unique'])) {
                $list['data'][$key]['has_one_member']['fans_item'] = substr($item['has_one_unique']['unionid'], 0, 4) . '**' . substr($item['has_one_unique']['unionid'], -6);
            }
            if (isset($item['has_one_mini_app'])) {
                $list['data'][$key]['has_one_member']['fans_item'] = substr($item['has_one_mini_app']['openid'], 0, 4) . '**' . substr($item['has_one_mini_app']['openid'], -6);
            }
            if (isset($item['has_one_fans'])) {
                $list['data'][$key]['has_one_member']['fans_item'] = substr($item['has_one_fans']['openid'], 0, 4) . '**' . substr($item['has_one_fans']['openid'], -6);
            }
            if (!empty($item['has_one_member']['mobile'])) {
                $list['data'][$key]['has_one_member']['fans_item'] = $item['has_one_member']['mobile'];
            }
            if (!empty($item['has_one_member']['nickname'])) {
                $list['data'][$key]['has_one_member']['fans_item'] = $item['has_one_member']['nickname'];
            }

            if (empty($item['has_one_member']['avatar']) && !empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['avatar'] = yz_tomedia(Setting::get('shop.member')['headimg_url']);
            }
            if (empty($item['has_one_member']['avatar']) && empty(Setting::get('shop.member')['headimg_url'])) {
                $list['data'][$key]['avatar'] = yz_tomedia(Setting::get('shop.shop')['logo']);
            }
        }
        return $list;
    }

}