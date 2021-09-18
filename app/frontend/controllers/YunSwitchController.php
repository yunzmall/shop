<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2021-03-29
 * Time: 17:11
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\frontend\controllers;


use app\common\components\BaseController;
use app\common\models\Option;
use app\common\models\SwitchModel;

class YunSwitchController extends BaseController
{
    public function index()
    {
        $switch = SwitchModel::where('switch_platform', 1)->first();
        if ($switch) {
            $plugin_is_open = Option::where(['uniacid'=>$switch->uniacid,'option_name'=>'yun-sign'])->first();
            if (!$plugin_is_open || $plugin_is_open->enabled == 0) {
                return $this->errorJson('指定平台芸签插件未开启');
            }

            return $this->successJson('ok', [
                'uniacid' => $switch->uniacid,
            ]);
        } else {
            return $this->errorJson('无平台开启芸签pc端');
        }
    }
}