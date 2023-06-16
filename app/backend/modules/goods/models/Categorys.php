<?php

namespace app\backend\modules\goods\models;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/22
 * Time: 下午2:24
 */

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class Categorys extends \app\common\models\Category
{
    protected $appends = ['url', 'procedures_url'];

    static protected $needLog = true;


    public static function getCategory()
    {

        return self::uniacid()->select('id', 'name', 'parent_id')
            ->where('plugin_id', '=', 0)->where('parent_id', '=', 0)
            ->with(['hasManyChildren' => function ($query) {
                $query->select('id', 'name', 'parent_id')
                    ->with(['hasManyChildren' => function ($query) {
                        $query->select('id', 'name', 'parent_id')->where('plugin_id', '=', 0);
                    }]);
            }])
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
    }

    public  static  function searchCategory($keyword)
    {
        global $setlevel;
        $setlevel = \Setting::get('shop.category.cat_level');
        return self::uniacid()
            ->select('id', 'name', 'parent_id', 'enabled', 'is_home', 'level')
            ->with(['hasManyChildren' => function ($query) use ($setlevel){
                $query->select('id', 'name', 'parent_id', 'enabled', 'is_home', 'level')
                    ->where('level', $setlevel)
                    ->with(['hasManyChildren' => function ($query) use ($setlevel) {
                        $query->select('id', 'name', 'parent_id', 'enabled', 'is_home', 'level')->where('level', $setlevel)->orderBy('display_order' ,'desc');
                    }])->orderBy('display_order' ,'desc');
            }])
            ->where('name', 'like', '%' . $keyword . '%')
            ->orderBy('display_order', 'desc')
            ->orderBy('id' ,'desc')
            ->pluginId()
            ->distinct()
            ->paginate(10);
    }

    public  static function getCategoryData()
    {
        global $setlevel;
        $setlevel = \Setting::get('shop.category.cat_level');  //根据后台设置显示两级还是三级分类

        return self::uniacid()
            ->select('id', 'name', 'parent_id', 'enabled', 'is_home')->with(['hasManyChildren' => function ($query) use ($setlevel){
                $query->select('id', 'name', 'parent_id', 'enabled', 'is_home')
                    ->with(['hasManyChildren' => function ($query) use ($setlevel) {
                        $query->select('id', 'name', 'parent_id', 'enabled', 'is_home')->where('level', $setlevel)->orderBy('display_order' ,'desc');
                    }])->orderBy('display_order' ,'desc');
            }])
            ->where('level', 1)
            ->orderBy('display_order' ,'desc')
            ->orderBy('id','desc')
            ->pluginId()
            ->paginate(10);
    }


    public function hasManyChildren()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function belongsToChildren()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }


    public function getUrlAttribute()
    {

        return yzAppFullUrl('catelist/' . $this->id);
    }

    public function getProceduresUrlAttribute()
    {

        return '/packageB/member/category/catelist/catelist?id=' . $this->id;
    }
}
