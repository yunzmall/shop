<?php

namespace app\backend\modules\setting\controllers;


use app\backend\modules\setting\models\Slide;
use app\common\components\BaseController;
use app\common\helpers\Url;
use Illuminate\Support\Facades\DB;
use app\common\models\Adv;

/**
* 商城广告
*/
class ShopAdvsController extends BaseController
{

	public function index()
	{
        $adv = Adv::first();
        if(request()->ajax() && request()->adv){
            $adv =  $adv ? $adv : (new Adv());
            $data['advs'] = request()->adv;
            unset($data['advs'][0]);
            $data['uniacid'] = \YunShop::app()->uniacid;
            $adv->fill($data);
            $bool = $adv->save();
            if (!$bool) {
                return $this->errorJson('广告位保存失败');
            }
            return $this->successJson('广告位保存成功');
        }
        //兼容旧数据，新的图片组件要多加个绝对路径的字段
        if($adv){
            $adv = $adv->toArray();
            foreach ($adv['advs'] as &$v){
                if(empty($v['img_url'])){
                    $v['img_url'] = yz_tomedia($v['img']);
                }
            }
        }

        return view('setting.adv.advertisement', [
            'adv' => json_encode($adv),
        ]);
	}

}