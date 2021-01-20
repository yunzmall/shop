<?php
/**
 * Created by PhpStorm.
 * User: 17812
 * Date: 2020/9/16
 * Time: 9:48
 */

namespace app\common\models\coupon;


use app\common\models\BaseModel;

class CouponSlideShow extends BaseModel
{
    /**
     * @var string
     */
    protected $table = "yz_coupon_slide_show";

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $appends = ['pic_url','status_name'];

    public function getPicUrlAttribute()
    {
        return yz_tomedia($this->attributes['slide_pic']);
    }

    public function getStatusNameAttribute()
    {
        return $this->attributes['is_show'] == 1?'显示':'隐藏';
    }

    public function rules()
    {
        return [
            'title' => 'required',
            'slide_pic' => 'required',
        ];
    }

    public function atributeNames()
    {
        return [
            'title' => '标题',
            'slide_pic' => '幻灯片图片',
        ];
    }
}