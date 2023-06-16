<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-11-29
 * Time: 23:52
 */

namespace app\backend\modules\withdraw\models;


use app\common\models\BaseModel;

class WithdrawRichText extends BaseModel
{
    public $table = 'yz_withdraw_rich_text';
    public $guarded = [''];
    public $timestamps = true;

    public static function createOrUpdate($data)
    {
        $model = self::uniacid()->first();
        if ($model) {
            $model->update([
                'content' => $data,
            ]);
        } else {
            $add_arr = [
                'uniacid' => \YunShop::app()->uniacid,
                'content' => $data,
            ];
            $model = self::create($add_arr);
        }
        return $model;
    }
}