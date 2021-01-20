<?php

namespace app\backend\modules\member\observers;


use app\common\models\MemberFavorite;
use app\common\traits\MessageTrait;
use Illuminate\Database\Eloquent\Model;


/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/28
 * Time: 上午11:24
 */
class MemberObserver extends \app\common\observers\BaseObserver
{
    use MessageTrait;
    
    public function saved(Model $model)
    {
        $this->pluginObserver('observer.member', $model, 'updated');
        //
    }





}