<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/9/9
 * Time: 17:39
 */

namespace app\backend\modules\goods\widget;

use app\backend\modules\goods\models\Privilege;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\member\models\MemberGroup;
use app\common\models\GoodsOption;

/**
 * 权限(非插件)
 */
class PrivilegeWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'privilege';

    public $code = 'auth';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $privilege = new Privilege();

        $goodsOption = [];
        if ($this->goods->id && Privilege::getInfo($this->goods->id)) {
            $privilege = Privilege::getInfo($this->goods->id);
//            $privilege->show_levels = (!empty($privilege->show_levels) || ($privilege->show_levels === 0 || $privilege->show_levels === '0')) ? explode(',', $privilege->show_levels) : '';
//            $privilege->buy_levels = (!empty($privilege->buy_levels) || ($privilege->buy_levels === 0 || $privilege->buy_levels === '0')) ? explode(',', $privilege->buy_levels) : '';
//            $privilege->show_groups = (!empty($privilege->show_groups) || ($privilege->show_groups === 0 || $privilege->show_groups === '0')) ? explode(',', $privilege->show_groups) : '';
//            $privilege->buy_groups = (!empty($privilege->buy_groups) || ($privilege->buy_groups === 0 || $privilege->buy_groups === '0')) ? explode(',', $privilege->buy_groups) : '';

            if ($this->goods->has_option) {
                $goodsOption = GoodsOption::select('id', 'title')
                    ->uniacid()
                    ->where('goods_id', $this->goods->id)->get()->toArray();
            }

        }

        array_unshift($goodsOption,['id'=>"",'title'=>'全部规格']);

        $levels = MemberLevel::getMemberLevelList();
        $groups = MemberGroup::getMemberGroupList();

        $data['goods_option'] = $goodsOption;

        $data['privilege'] = $privilege?$privilege->toArray():[];  //会员限购
        $data['levels'] = $levels; //会员等级
        array_unshift($data['levels'],['id'=>0,'level_name'=>'普通等级']);
        array_unshift($data['levels'],['id'=>'','level_name'=>'全部会员等级']);

        $data['groups'] = $groups; //会员组
        array_unshift($data['groups'],['id'=>0,'group_name'=>'无分组']);
        array_unshift($data['groups'],['id'=>'','group_name'=>'全部会员组']);

        $data['is_show_buy_limit'] = false;
        if (in_array($this->goods->plugin_id,[0,31,32,44,120])) $data['is_show_buy_limit'] = true;

        //dd($data);

        return $data;
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}