<?php
/******************************************************************************************************************
 * Author:  king -- LiBaoJia
 * Date:    10/26/21 3:24 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * 
 * 
 * 
 ******************************************************************************************************************/


namespace app\common\observers;


use app\common\events\member\MemberChangeEvent;
use Illuminate\Database\Eloquent\Model;

class McMemberObserver extends BaseObserver
{
    public function updated(Model $model)
    {
        event(new MemberChangeEvent($model));
    }
}
