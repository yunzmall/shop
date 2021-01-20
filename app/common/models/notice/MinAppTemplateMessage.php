<?php

/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/11/9
 * Time: 下午3:09
 */

namespace app\common\models\notice;


use app\common\models\BaseModel;
use app\common\scopes\UniacidScope;
use Illuminate\Database\Eloquent\Builder;
use app\backend\models\BackendModel;

class MinAppTemplateMessage extends BackendModel
{
    public $table = 'yz_mini_app_template_message';


    protected $guarded = [''];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new UniacidScope);
    }


    public static function getList()
    {
        return self::select('*')->get();
    }

    public static function getSmallType()
    {
        return self::uniacid()->select('*')->where("small_type",1)->get();
    }

    public static function getAllTemp()
    {
        return self::select('id','title')->get();
    }

    public static function getTemp($id)
    {
        return self::select()->where('template_id',$id)->first();
    }
    public static function getTitle($title)
    {
        return self::select('template_id','is_open')->where('title',$title)->where('is_default',1)->first();
    }
    public static function getOpenTemp($id)
    {
        return self::select()->where('id',$id)->where('is_default',1)->first();
    }
    public static function delTempDataByTempId($id)
    {
        return self::where('id',$id)->delete();
    }

    public static function isOpen($id,$open)
    {
        return self::where('id',$id)->update(['is_open' => $open]);
    }

    public static function getTempById($temp_id)
    {
        return self::select('template_id')->whereId($temp_id);
    }
    public static function getTemplate($id)
    {
        return self::select('template_id')->where('id',$id)->first();
    }

    public static function fetchTempList($kwd)
    {
        return self::select()->where('is_default',0)->likeTitle($kwd);
    }

    public function scopeGetTempList($query)
    {
        $data = $this->getTempListTitle();
        return $query->whereIn('title', $data)->where("small_type",1)->groupBy('title')->get();
    }

    private function getTempListTitle()
    {
        return [
          '新订单提醒','订单支付成功通知','订单发货通知','确认收货通知','订单退款通知','佣金到账提醒','粉丝下单通知','审核结果通知','提现状态通知','提现成功通知'
        ];
    }

//    public function scopeLikeTitle($query, $kwd)
//    {
//        return $query->where('title', 'like', '%' . $kwd . '%');
//    }

}